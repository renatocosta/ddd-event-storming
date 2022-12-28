<?php

namespace Infrastructure\Framework\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Interfaces\Incoming\WebApi\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {

        $this->routes(function () {
            Route::namespace($this->namespace)
                ->group(__DIR__ . '/../Routes/api.php');
        });

        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(env('RATE_LIMIT_PER_MINUTE'));
        });

        RateLimiter::for('sms-code-resender', function (Request $request) {
            return Limit::perMinute(5);
        });
    }
}
