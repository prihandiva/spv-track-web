<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
    ];

    /**
     * Get setting value by key with fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value,
        };
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        $storedValue = is_array($value) ? json_encode($value) : (string) $value;

        if ($setting) {
            $setting->update(['value' => $storedValue]);
        } else {
            static::create([
                'key' => $key,
                'value' => $storedValue,
                'label' => ucwords(str_replace('_', ' ', $key)),
            ]);
        }
    }

    /**
     * Get settings grouped by section.
     */
    public static function getGrouped(): Collection
    {
        return static::all()->groupBy('group');
    }
}
