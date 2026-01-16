<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdminAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check() && Auth::guard('web')->check()) {
            return redirect()->route('dashboard')->with(['error'=>'page that you are trying to found does not exist']);
        }
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('adminForm')->withErrors(['Access denied']);
        }
        $user = Auth::guard('admin')->user();

        if ($user) {
            return $next($request);
        }
        // If the user is not a vendor, abort with a 403 error
        return abort(403, 'Unauthorized access');

        if (!Auth::guard('admin')->check() && Auth::guard('web')->check()) {
            return redirect()->route('loginForm')
                ->with('error', 'Access denied for seller');
        }
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('adminForm')
                ->withErrors(['Access denied']);
        }

        return $next($request);
    }
}
