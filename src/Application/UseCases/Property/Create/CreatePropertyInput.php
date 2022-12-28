<?php

namespace Application\UseCases\Property\Create;

final class CreatePropertyInput
{

    public function __construct(public int $customerId, public string $address, public string $zipcode, public string $state, public string $bedrooms, public string $city, public string $extraDetails, public string $bathrooms, public string $size, public string $lat, public string $long)
    {
    }
}
