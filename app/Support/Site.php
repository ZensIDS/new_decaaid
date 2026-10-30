<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class Site
{
    /** @return array<int, string> */
    public static function locales(): array
    {
        return config('site.locales');
    }

    public static function url(string $key, array $params = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route($locale.'.'.$key, $params);
    }

    /**
     * URL halaman saat ini dalam bahasa $target.
     * Jika padanan route tidak ada, kembali ke beranda bahasa tujuan.
     */
    public static function switchUrl(string $target): string
    {
        $current = app()->getLocale();
        $route = request()->route();
        $name = $route?->getName();

        if ($name && str_starts_with($name, $current.'.')) {
            $key = substr($name, strlen($current) + 1);
            $targetName = $target.'.'.$key;

            if (Route::has($targetName)) {
                $url = route($targetName, $route->parameters());
                $query = request()->getQueryString();

                return $query ? $url.'?'.$query : $url;
            }
        }

        return route($target.'.home');
    }
}