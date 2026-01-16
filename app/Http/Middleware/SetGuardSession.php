<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
class SetGuardSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect which area based on URL prefix
        if ($request->is('admin/*')) {
            config(['session.cookie' => 'pm_admin_session']);
        } elseif ($request->is('seller/*')) {
            config(['session.cookie' => 'pm_seller_session']);
        } else {
            config(['session.cookie' => 'pm_default_session']);
        }

        return $next($request);
    }
}
