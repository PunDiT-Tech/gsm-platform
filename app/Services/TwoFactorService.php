<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TwoFactorService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    private const DIGITS = 6;

    private const WINDOW = 1;

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function codeFor(string $secret, ?int $timestamp = null): string
    {
        $counter = intdiv($timestamp ?? time(), self::PERIOD);
        $hash = hash_hmac('sha1', pack('N*', 0, $counter), $this->base32Decode($secret), true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = trim($code);

        if (! preg_match('/^[0-9]{' . self::DIGITS . '}$/', $code)) {
            return false;
        }

        $timestamp = time();

        for ($window = -self::WINDOW; $window <= self::WINDOW; $window++) {
            $expected = $this->codeFor($secret, $timestamp + ($window * self::PERIOD));

            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    public function otpauthUri(User $user): string
    {
        return $this->otpauthUriForSecret((string) $user->email, (string) $user->two_factor_secret);
    }

    public function otpauthUriForSecret(string $email, string $secret): string
    {
        return 'otpauth://totp/' . rawurlencode($email)
            . '?secret=' . $this->normalizeSecret($secret)
            . '&issuer=' . rawurlencode((string) config('app.name'))
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    public function generateRecoveryCodes(int $count = 10): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $random = Str::upper(Str::random(12));
            $codes[] = substr($random, 0, 4) . '-' . substr($random, 4, 4) . '-' . substr($random, 8, 4);
        }

        return $codes;
    }

    public function recover(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        $normalized = strtoupper(trim($code));

        foreach ($codes as $index => $stored) {
            if (hash_equals((string) $stored, $normalized)) {
                $remaining = $codes;
                unset($remaining[$index]);
                $user->update(['two_factor_recovery_codes' => array_values($remaining)]);

                return true;
            }
        }

        return false;
    }

    public function isEnabled(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null && $user->two_factor_secret !== null;
    }

    private function normalizeSecret(string $secret): string
    {
        return $this->base32Encode($this->base32Decode($secret));
    }

    private function base32Encode(string $data): string
    {
        $result = '';
        $bits = 0;
        $value = 0;

        foreach (str_split($data) as $byte) {
            $value = ($value << 8) | ord($byte);
            $bits += 8;

            while ($bits >= 5) {
                $result .= self::BASE32_ALPHABET[($value >> ($bits - 5)) & 0x1F];
                $bits -= 5;
            }
        }

        if ($bits > 0) {
            $result .= self::BASE32_ALPHABET[($value << (5 - $bits)) & 0x1F];
        }

        return $result;
    }

    private function base32Decode(string $data): string
    {
        $data = strtoupper(trim($data));
        $data = rtrim($data, '=');

        $result = '';
        $bits = 0;
        $value = 0;

        foreach (str_split($data) as $char) {
            $position = strpos(self::BASE32_ALPHABET, $char);

            if ($position === false) {
                continue;
            }

            $value = ($value << 5) | $position;
            $bits += 5;

            if ($bits >= 8) {
                $result .= chr(($value >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }

        return $result;
    }
}