<?php

namespace Infrastructure\Framework\Providers;

use Faker\Provider\Base;
use Infrastructure\Framework\Entities\PropertiesModel;
use Mockery;

class PropertyFakerProvider extends Base
{

    public function propertyModel()
    {
        $model = Mockery::mock(new PropertiesModel());

        $model->shouldReceive('create')->andReturn(new class($this->generator)
        {
            public $id = 123456789;
            public $customer_id = 1233126;
            public $address;
            public $zipcode = '09020090';
            public $state = 'Hawaii';
            public $number_of_bedrooms = 55;
            public $city = 'honolulu';
            public $extra_details = 'ssljlkj3';
            public $number_of_bathrooms = 55;
            public $size = 10;
            public $latitude;
            public $longitude;

            public function __construct($generator)
            {
                $this->address = $generator->address();
                $this->latitude = $generator->latitude();
                $this->longitude = $generator->longitude();
            }
        });

        return $model;
    }
}
