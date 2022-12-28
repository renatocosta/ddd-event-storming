<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Faker\{Factory, Generator};
use Infrastructure\Framework\Providers\CommonFakerProvider;
use Infrastructure\Framework\Providers\CustomerFakerProvider;
use Infrastructure\Framework\Providers\OrderFakerProvider;
use Infrastructure\Framework\Providers\ProjectReportsFakerProvider;
use Infrastructure\Framework\Providers\PropertyFakerProvider;
use Infrastructure\Framework\Providers\UserFakerProvider;

class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Generator::class, function () {
            $faker = Factory::create('en_US');
            $faker->addProvider(new CommonFakerProvider($faker));
            $userFakerProvider  = new UserFakerProvider($faker);
            $orderFakerProvider = new OrderFakerProvider($faker, $userFakerProvider);
            $customerFakerProvider  = new CustomerFakerProvider($faker);
            $propertyFakerProvider  = new PropertyFakerProvider($faker);

            $faker->addProvider($orderFakerProvider);
            $faker->addProvider(new ProjectReportsFakerProvider($faker, $orderFakerProvider, $userFakerProvider));
            $faker->addProvider($userFakerProvider);
            $faker->addProvider($customerFakerProvider);
            $faker->addProvider($propertyFakerProvider);

            return $faker;
        });
    }
}
