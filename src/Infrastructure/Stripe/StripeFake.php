<?php

namespace Infrastructure\Stripe;

use Mockery;
use Stripe\BaseStripeClient;
use Stripe\Customer;
use Stripe\PaymentMethod;

final class StripeFake extends BaseStripeClient
{

    public $customers;

    public $paymentMethods;

    public function __construct()
    {
        $this->customers = Mockery::mock(Customer::class, function ($mock) {
            $mock->shouldReceive('create')->andReturn(
                new class
                {
                    public $id = 'sss';
                }
            );
        });

        $this->paymentMethods = Mockery::mock(PaymentMethod::class, function ($mock) {
            $mock->shouldReceive('attach');
        });
    }
}
