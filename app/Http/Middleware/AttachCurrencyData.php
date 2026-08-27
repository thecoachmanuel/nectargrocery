<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachCurrencyData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setting = generaleSetting('setting');
        $currencyId = $request->currency_id ?? $setting?->currency_id ?? 1;

        $cData = currencyData($currencyId) ?? [
            'id' => 1,
            'name' => 'USD',
            'symbol' => '$',
            'rate' => 1,
            'is_default' => true,
        ];

        $request->merge([
            'currencyData' => $cData,
        ]);

        return $next($request);
    }
}
