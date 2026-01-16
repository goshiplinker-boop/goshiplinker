<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        $utm_data = [];
        foreach ($utmParams as $param) {
            if ($request->has($param)) {
                $utm_data[$param] = $request->query($param);
               
            }
        }
        if(!empty($utm_data)){
            session(['utm_data' => $utm_data]);
        }
        return $next($request);
    }
}
