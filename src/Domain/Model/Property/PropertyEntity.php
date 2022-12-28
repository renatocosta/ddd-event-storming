<?php

namespace Domain\Model\Property;

use Common\ValueObjects\AggregateRoot;
use Common\ValueObjects\Geography\Address;
use Common\ValueObjects\Geography\City;
use Common\ValueObjects\Geography\State;
use Common\ValueObjects\Geography\Zipcode;
use Common\ValueObjects\Misc\Bathrooms;
use Common\ValueObjects\Misc\Bedrooms;
use Common\ValueObjects\Misc\Coordinates;
use Common\ValueObjects\Misc\ExtraDetails;
use Common\ValueObjects\Misc\Size;
use Domain\Model\Property\Events\PropertyCreated;

final class PropertyEntity extends AggregateRoot implements Property
{

    private int $id;

    private int $customerId;

    private Address $address;

    private Zipcode $zipcode;

    private State $state;

    private Bedrooms $bedrooms;

    private City $city;

    private ExtraDetails $extraDetails;

    private Bathrooms $bathrooms;

    private Size $size;

    private Coordinates $coordinates;

    public function getId(): int
    {
        return $this->id;
    }

    public function create(): void
    {
        $this->raise(new PropertyCreated($this));
    }

    public function fromExisting(int $id, int $customerId, Address $address, Zipcode $zipcode, State $state, Bedrooms $bedrooms, City $city, ExtraDetails $extraDetails, Bathrooms $bathrooms, Size $size, Coordinates $coordinates): self
    {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->address = $address;
        $this->zipcode = $zipcode;
        $this->state = $state;
        $this->bedrooms = $bedrooms;
        $this->city = $city;
        $this->extraDetails = $extraDetails;
        $this->bathrooms = $bathrooms;
        $this->size = $size;
        $this->coordinates = $coordinates;

        return $this;
    }

    public function from(int $customerId, Address $address, Zipcode $zipcode, State $state, Bedrooms $bedrooms, City $city, ExtraDetails $extraDetails, Bathrooms $bathrooms, Size $size, Coordinates $coordinates): self
    {
        $this->customerId = $customerId;
        $this->address = $address;
        $this->zipcode = $zipcode;
        $this->state = $state;
        $this->bedrooms = $bedrooms;
        $this->city = $city;
        $this->extraDetails = $extraDetails;
        $this->bathrooms = $bathrooms;
        $this->size = $size;
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function zipcode(): Zipcode
    {
        return $this->zipcode;
    }

    public function state(): State
    {
        return $this->state;
    }

    public function bedrooms(): Bedrooms
    {
        return $this->bedrooms;
    }

    public function city(): City
    {
        return $this->city;
    }

    public function extraDetails(): ExtraDetails
    {
        return $this->extraDetails;
    }

    public function bathrooms(): Bathrooms
    {
        return $this->bathrooms;
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function coordinates(): Coordinates
    {
        return $this->coordinates;
    }
}
