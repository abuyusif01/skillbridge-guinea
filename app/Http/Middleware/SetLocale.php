<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('skillbridge_locale');

        app()->setLocale(
            in_array($locale, ['en', 'fr'], true) ? $locale : config('app.locale')
        );

        return $next($request);
    }
}
