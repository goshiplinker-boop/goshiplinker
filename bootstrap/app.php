<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\SellerAuthentication;
use App\Http\Middleware\AdminAuthentication;
use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Middleware\SetGuardSession;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(SetGuardSession::class);
        $middleware->appendToGroup('isAdmin', [
            AdminAuthentication::class
        ]);
        $middleware->appendToGroup('isSeller', [
            SellerAuthentication::class
        ]);
        $middleware->appendToGroup('captureUtm', [
            CaptureUtmParameters::class
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handling Authentication Exception
        $exceptions->render(function (AuthenticationException $exception) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing token',
                ], 401); // 401 Unauthorized
            }
            // Redirect for web routes
            return redirect()->guest(route('login'));
        });

        // Handling NotFoundHttpException
        $exceptions->render(function (NotFoundHttpException $exception) {           
            // Check if the user is authenticated
            if (!Auth::check()) {
                // Redirect to the login page if the user is not logged in
                return response()->view('errors.500', [], 500); // Adjust this route name as necessary
            }

            // If the user is logged in, return a custom 404 view
            return response()->view('errors.404', [], 404);
        });

        // Catch-all for any other exceptions
        $exceptions->render(function (Exception $exception) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred',
                ], 500); // 500 Internal Server Error
            }

            // Custom fallback for web routes, e.g., returning a 500 error view
            //return response()->view('errors.500', ['exception' => $exception], 500);
        });
    })->create();