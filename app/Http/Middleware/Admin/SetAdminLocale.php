<?php

namespace App\Http\Middleware\Admin;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Antarmuka admin dikunci ke Bahasa Indonesia (keputusan K29). */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

        return $next($request);
    }
}