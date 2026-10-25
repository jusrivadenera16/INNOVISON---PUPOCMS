<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ClinicBranding
{
    private const SETTING_KEY = 'clinic_logo_path';
    private const DEFAULT_LOGO = 'images/clinic_logo_transparent.png';
    private const LOGO_PATH_PATTERN = '/\Auploads\/clinic-branding\/clinic-logo-[0-9a-f-]+\.(?:png|jpe?g|webp)\z/i';

    public static function logoPath(): string
    {
        $path = Cache::rememberForever('clinic_branding.logo_path', function () {
            return Schema::hasTable('system_settings')
                ? SystemSetting::getValue(self::SETTING_KEY)
                : null;
        });

        if (!is_string($path) || !self::isManagedLogoPath($path) || !is_file(public_path($path))) {
            return self::DEFAULT_LOGO;
        }

        return $path;
    }

    public static function logoUrl(): string
    {
        $path = self::logoPath();
        $url = asset($path);

        if ($path === self::DEFAULT_LOGO && is_file(public_path($path))) {
            return $url . '?v=' . filemtime(public_path($path));
        }

        return $url;
    }

    public static function isManagedLogoPath(string $path): bool
    {
        return preg_match(self::LOGO_PATH_PATTERN, $path) === 1;
    }

    public static function forgetCachedLogoPath(): void
    {
        Cache::forget('clinic_branding.logo_path');
    }
}
