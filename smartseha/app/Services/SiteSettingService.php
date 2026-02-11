<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;

class SiteSettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = SiteSetting::query()->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        if ($setting->is_encrypted && !empty($setting->value)) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $setting->value ?? $default;
    }

    public function set(string $key, mixed $value, bool $encrypted = false): void
    {
        $serialized = is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);

        if ($encrypted && $serialized !== '') {
            $serialized = Crypt::encryptString($serialized);
        }

        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $serialized,
                'is_encrypted' => $encrypted,
            ]
        );
    }

    public function getInt(string $key, int $default): int
    {
        return (int) ($this->get($key, $default) ?? $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }
}
