<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use YlsIdeas\FeatureFlags\Facades\Features;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\RouteServiceProvider::class);
        $this->app->register(\App\Providers\FakerServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\CommonServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\UseCaseOrchestratorServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\OrderServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\UserServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\NotificationServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\ProjectReportsServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\CustomerServiceProvider::class);
        $this->app->register(\Infrastructure\Framework\Providers\PropertyServiceProvider::class);            
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Features::noBlade();
        Features::noScheduling();
        Features::noValidations();
        Features::noCommands();
    }
}
