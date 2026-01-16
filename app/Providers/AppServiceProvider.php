<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
          // URL::forceScheme('https');
        }
        // Use a default pagination view (optional, if you have custom pagination views)
        Paginator::defaultView('vendor.pagination.bootstrap-5');

        // Set the global pagination count
        Paginator::useBootstrap(); // or Tailwind, depending on your setup

        // Prevent lazy loading in development environment (good practice)
        Model::preventLazyLoading(!app()->isProduction());
    }
}
