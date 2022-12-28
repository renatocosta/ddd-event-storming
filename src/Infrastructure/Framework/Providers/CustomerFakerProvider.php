<?php

namespace Infrastructure\Framework\Providers;

use Faker\Provider\Base;
use Infrastructure\Framework\Entities\CustomerOrderLocationsModel;
use Infrastructure\Framework\Entities\CustomerOrderPaymentsModel;
use Infrastructure\Framework\Entities\CustomersModel;
use Mockery;

class CustomerFakerProvider extends Base
{

    public function customerModel()
    {
        $model = Mockery::mock(new CustomersModel());
        $model->name = $this->generator->name();
        $model->email = $this->generator->email();
        $model->mobile = $this->generator->phoneNumber;
        $model->country_code = $this->generator->countryCode();
        $model->shouldReceive('updateOrCreate')->andReturn(new class
        {
            public $id = 123444;
        });

        return $model;
    }

    public function customerOrderLocationModel()
    {
        $model = Mockery::mock(new CustomerOrderLocationsModel());
        $model->timezone = $this->generator->timezone();
        $model->order_id = $this->generator->uuid();
        $model->customer_id = 12336;
        $model->shouldReceive('create');
        return $model;
    }

    public function customerOrderPaymentModel()
    {
        $model = Mockery::mock(new CustomerOrderPaymentsModel());
        $model->payment_method_token = 'pay_ci728278278';
        $model->card_number_last4 = '2413';
        $model->customer_token = 'cust_kdhdhjk2222122';
        $model->order_id = $this->generator->uuid();
        $model->customer_id = 12336;
        $model->shouldReceive('create');
        return $model;
    }
}
