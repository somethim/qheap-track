<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        app()->setLocale($this->getLocale($request));

        return $next($request);
    }

    private function getLocale($request): string
    {
        if (auth()->check()) {
            if ($sessionLocale = session('user_locale')) {
                return $sessionLocale;
            }

            if ($userLocale = auth()->user()->locale) {
                session(['user_locale' => $userLocale]);

                return $userLocale;
            }
        }

        if ($request->get('locale') !== null) {
            return $request->get('locale');
        }

        return config('app.locale');
    }
}
