<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value] // ✅ Ensure stored as string
        );
    }

    /**
     * Get expiration days as integer
     */
    public static function getExpirationDays(): int
    {
        return (int) self::get('expiration_days', 30); // ✅ Force cast to int
    }
}