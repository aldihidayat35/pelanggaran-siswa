<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Get all settings as key-value array.
     */
    public static function getAllAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Get settings grouped by group.
     */
    public static function getGrouped()
    {
        return static::all()->groupBy('group');
    }
}
