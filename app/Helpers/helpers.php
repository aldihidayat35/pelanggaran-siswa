<?php

use App\Models\AppSetting;

if (!function_exists('app_setting')) {
    /**
     * Get an application setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function app_setting(string $key, $default = null)
    {
        try {
            return AppSetting::getValue($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}
