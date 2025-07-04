<?php

namespace App\Http\Middleware;

use App\Facades\LibrenmsConfig;
use App\Models\UserPref;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifyTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check twofactor
        if (auth()->check() && LibrenmsConfig::get('twofactor') === true) {
            // don't apply on 2fa checking routes
            $route_name = $request->route()->getName();
            if ($route_name && Str::startsWith($route_name, '2fa.')) {
                return $next($request);
            }

            $twofactor = $request->session()->get('twofactoradd', UserPref::getPref($request->user(), 'twofactor'));

            if (! empty($twofactor)) {
                // user has 2fa enabled
                if (! $request->session()->get('twofactor')) {
                    // verification is needed
                    return redirect('/2fa');
                }
            }
        }

        return $next($request);
    }
}
