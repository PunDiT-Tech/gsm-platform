<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFieldValue;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data, ?\Illuminate\Contracts\Auth\Authenticatable $user = null): Order
    {
        $service = Service::public()->where('slug', $data['service_slug'])->first();

        if (! $service) {
            throw ValidationException::withMessages(['service_slug' => 'This service is not available.']);
        }

        if ($service->service_type === 'EXTERNAL' || ! $service->is_active) {
            throw ValidationException::withMessages(['service_slug' => 'This service cannot be ordered online.']);
        }

        $this->validateDynamicFields($service, $data['fields'] ?? []);

        if ($service->consent_required && empty($data['consent'])) {
            throw ValidationException::withMessages(['consent' => 'You must confirm that you are authorized to request this service.']);
        }

        $this->guardDuplicateSubmission($service, $data['fields'] ?? [], $user, $data);

        $customer = $this->resolveCustomer($data, $user);

        $coupon = app(CouponService::class)->resolve($data['coupon_code'] ?? null, $customer, $service);

        $basePrice = $service->service_type === 'FREE' ? 0 : (float) $service->price;
        $price = app(CouponService::class)->applyDiscount($basePrice, $coupon);
        $paymentRequired = $service->service_type === 'PAID' || ($service->service_type === 'STANDARD' && $service->payment_required) || $price > 0;

        $trackingToken = Str::random(40);

        $order = DB::transaction(function () use ($service, $customer, $data, $price, $paymentRequired, $trackingToken, $coupon) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'tracking_token' => Hash::make($trackingToken),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'service_name_snapshot' => $service->name,
                'price_snapshot' => $price,
                'currency_snapshot' => $service->currency,
                'status' => 'PENDING',
                'payment_status' => $paymentRequired ? 'UNPAID' : 'VERIFIED',
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'coupon_code' => $coupon?->code ?? null,
                'consent_given_at' => ($service->consent_required && ! empty($data['consent'])) ? now() : null,
                'expires_at' => $paymentRequired ? now()->addHours((int) config('app.order_expiry_hours', 24)) : null,
            ]);

            foreach ($service->activeFields as $field) {
                $raw = $data['fields'][$field->id] ?? null;
                $path = null;

                if ($field->isFileType() && $raw instanceof \Illuminate\Http\UploadedFile) {
                    $path = $raw->store('order-files', 'local');
                    $valueText = '';
                } else {
                    $valueText = $field->type === 'CHECKBOX' || $field->type === 'MULTI_SELECT'
                        ? (is_array($raw) ? implode(', ', $raw) : (string) $raw)
                        : (string) $raw;
                }

                OrderFieldValue::create([
                    'order_id' => $order->id,
                    'service_field_id' => $field->id,
                    'label' => $field->label,
                    'value' => $valueText,
                    'file_path' => $path,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'PENDING',
                'created_at' => now(),
            ]);

            if ($paymentRequired) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $price,
                    'currency' => $service->currency,
                    'status' => 'UNPAID',
                ]);
            }

            return $order;
        });

        if ($coupon) {
            app(CouponService::class)->recordUsage($order, $coupon, $basePrice);
        }

        $order->tracking_code_plain = $trackingToken;

        if ($customer->user_id) {
            \App\Models\User::find($customer->user_id)?->notify(new \App\Notifications\OrderConfirmationNotification($order));
        }

        \App\Jobs\SendTelegramOrderNotification::dispatch($order, 'new_order');

        return $order;
    }

    protected function validateDynamicFields(Service $service, array $fields): void
    {
        $validated = [];

        foreach ($service->activeFields as $field) {
            $name = 'fields.' . $field->id;
            $rules = [];

            if ($field->is_required) {
                $rules[] = $field->isFileType() ? 'required|file' : 'required';
            } else {
                $rules[] = 'nullable';
            }

            switch ($field->type) {
                case 'TEXTAREA':
                    $rules[] = 'string';
                    break;
                case 'NUMBER':
                    $rules[] = 'numeric';
                    break;
                case 'EMAIL':
                    $rules[] = 'email';
                    break;
                case 'URL':
                    $rules[] = 'url';
                    break;
                case 'FILE':
                    $rules[] = 'max:10240|mimes:jpg,jpeg,png,pdf';
                    break;
                case 'IMEI':
                    $rules[] = $field->validation_regex ? "regex:{$field->validation_regex}" : 'regex:/^[0-9]{15}$/';
                    break;
                case 'PHONE':
                    $rules[] = $field->validation_regex ? "regex:{$field->validation_regex}" : 'regex:/^[0-9+\-() ]{7,20}$/';
                    break;
                case 'SERIAL_NUMBER':
                    $rules[] = $field->validation_regex ? "regex:{$field->validation_regex}" : 'regex:/^[A-Za-z0-9_-]{4,64}$/';
                    break;
                default:
                    $rules[] = 'string';
            }

            if ($field->max_length) {
                $rules[] = "max:{$field->max_length}";
            }
            if ($field->min_length) {
                $rules[] = "min:{$field->min_length}";
            }

            if ($field->isSelectType()) {
                $rules[] = in_array($field->type, ['MULTI_SELECT', 'CHECKBOX']) ? 'array' : 'string';
            }

            $validated[$name] = $rules;
        }

        $validator = \Illuminate\Support\Facades\Validator::make(['fields' => $fields], $validated);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function extractFieldValue(array $fields, ServiceField $field): mixed
    {
        $value = $fields[$field->id] ?? null;

        if ($field->isFileType()) {
            return ['file' => $value];
        }

        if ($field->type === 'CHECKBOX' || $field->type === 'MULTI_SELECT') {
            return is_array($value) ? implode(', ', $value) : $value;
        }

        return $value;
    }

    protected function guardDuplicateSubmission(Service $service, array $fields, ?\Illuminate\Contracts\Auth\Authenticatable $user, array $data): void
    {
        $identifier = null;

        foreach ($service->activeFields as $field) {
            if (in_array($field->type, ['IMEI', 'SERIAL_NUMBER'])) {
                $identifier = $fields[$field->id] ?? null;
                break;
            }
        }

        if (! $identifier) {
            return;
        }

        $email = $data['customer_email'] ?? $user?->email;

        if (! $email) {
            return;
        }

        $recent = Order::where('customer_email', $email)
            ->where('service_id', $service->id)
            ->whereIn('status', ['PENDING', 'PROCESSING', 'WAITING_FOR_CUSTOMER'])
            ->where('created_at', '>', now()->subMinutes(10))
            ->whereHas('fieldValues', fn ($q) => $q->where('value', $identifier))
            ->exists();

        if ($recent) {
            throw ValidationException::withMessages([
                'fields' => 'An order for this device is already in progress. Please check your existing order instead of submitting a duplicate.',
            ]);
        }
    }

    protected function resolveCustomer(array $data, ?\Illuminate\Contracts\Auth\Authenticatable $user): Customer
    {
        if ($user && ($customer = Customer::where('user_id', $user->id)->first())) {
            return $customer;
        }

        $name = $data['customer_name'] ?? $user?->name;
        $email = $data['customer_email'] ?? $user?->email;
        $phone = $data['customer_phone'] ?? $user?->phone;

        $customer = Customer::where('email', $email)->first();

        if (! $customer) {
            $customer = Customer::create([
                'user_id' => $user?->id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'guest_token' => $user ? null : Str::random(60),
            ]);
        }

        return $customer;
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = strtoupper('ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
