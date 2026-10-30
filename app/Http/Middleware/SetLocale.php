<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Menetapkan bahasa aplikasi sesuai prefiks URL.
     * Dipakai sebagai: SetLocale::class.':id' atau SetLocale::class.':en'
     */
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        if (! in_array($locale, config('site.locales'), true)) {
            abort(404);
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}