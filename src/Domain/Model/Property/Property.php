<?php

namespace Domain\Model\Property;

use Common\ValueObjects\Geography\Address;
use Common\ValueObjects\Geography\City;
use Common\ValueObjects\Geography\State;
use Common\ValueObjects\Geography\Zipcode;
use Common\ValueObjects\Misc\Bathrooms;
use Common\ValueObjects\Misc\Bedrooms;
use Common\ValueObjects\Misc\Coordinates;
use Common\ValueObjects\Misc\ExtraDetails;
use Common\ValueObjects\Misc\Size;

interface Property
{

    public function getId(): int;

    public function create(): void;

    public function fromExisting(int $id, int $customerId, Address $address, Zipcode $zipcode, State $state, Bedrooms $bedrooms, City $city, ExtraDetails $extraDetails, Bathrooms $bathrooms, Size $size, Coordinates $coordinates): self;

    public function from(int $customerId, Address $address, Zipcode $zipcode, State $state, Bedrooms $bedrooms, City $city, ExtraDetails $extraDetails, Bathrooms $bathrooms, Size $size, Coordinates $coordinates): self;

    public function getCustomerId(): int;

    public function address(): Address;

    public function zipcode(): Zipcode;

    public function state(): State;

    public function bedrooms(): Bedrooms;

    public function city(): City;

    public function extraDetails(): ExtraDetails;

    public function bathrooms(): Bathrooms;

    public function size(): Size;

    public function coordinates(): Coordinates;
}
