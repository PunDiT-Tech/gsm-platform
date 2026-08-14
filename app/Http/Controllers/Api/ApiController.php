<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function services(): JsonResponse
    {
        $services = Service::public()
            ->with('category:id,name,slug')
            ->get(['id', 'external_id', 'name', 'slug', 'short_description', 'price', 'currency', 'service_type', 'is_active']);

        return response()->json(['data' => $services]);
    }

    public function service(string $slug): JsonResponse
    {
        $service = Service::public()->where('slug', $slug)->first();

        if (! $service) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => $service->load(['category:id,name,slug'])
                ->makeHidden(['description']),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:64'],
            'tracking_code' => ['required', 'string', 'max:64'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->with(['service:id,name,slug', 'fieldValues', 'statusHistory', 'payments'])
            ->first();

        if (! $order || ! Hash::check($validated['tracking_code'], $order->tracking_token)) {
            return response()->json(['error' => 'Invalid order number or tracking code.'], 404);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'external_id' => $order->external_id,
                'service' => $order->service?->name,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'price' => $order->price_snapshot,
                'currency' => $order->currency_snapshot,
                'created_at' => $order->created_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->toIso8601String(),
            ],
        ]);
    }
}