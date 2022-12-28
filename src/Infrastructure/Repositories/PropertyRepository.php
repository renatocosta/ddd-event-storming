<?php

namespace Infrastructure\Repositories;

use Common\ValueObjects\Geography\Address;
use Common\ValueObjects\Geography\City;
use Common\ValueObjects\Geography\State;
use Common\ValueObjects\Geography\Zipcode;
use Common\ValueObjects\Misc\Bathrooms;
use Common\ValueObjects\Misc\Bedrooms;
use Common\ValueObjects\Misc\Coordinates;
use Common\ValueObjects\Misc\ExtraDetails;
use Common\ValueObjects\Misc\Size;
use Domain\Model\Property\IPropertyRepository;
use Domain\Model\Property\Property;
use Domain\Model\Property\PropertyEntity;
use Infrastructure\Framework\Entities\PropertiesModel;

final class PropertyRepository implements IPropertyRepository
{

    public function __construct(
        private PropertiesModel $propertyModelWriter,
        private PropertiesModel $propertyModelReader
    ) {
    }

    public function getById(int $id): Property
    {
        $property = $this->propertyModelReader
            ->findOrFail($id);

        return (new PropertyEntity())->fromExisting($property->id, $property->customerId, new Address($property->address), new Zipcode($property->zipcode), new State($property->state), new Bedrooms($property->bedrooms), new City($property->city), new ExtraDetails($property->extraDetails), new Bathrooms($property->bathrooms), new Size($property->size), new Coordinates($property->lat, $property->long));
    }

    public function create(object $property): void
    {
        $propertyCreated = $this->propertyModelWriter
            ->create(['customer_id' => $property->getCustomerId(), 'address' => $property->address(), 'zipcode' => $property->zipcode(), 'state' => $property->state(), 'number_of_bedrooms' => $property->bedrooms(), 'city' => $property->city(), 'extra_details' => $property->extraDetails(), 'number_of_bathrooms' => $property->bathrooms(), 'size' => $property->size(), 'latitude' => $property->coordinates()->lat(), 'longitude' => $property->coordinates()->lon()]);
        $property->fromExisting($propertyCreated->id, $propertyCreated->customer_id, new Address($propertyCreated->address), new Zipcode($propertyCreated->zipcode), new State($propertyCreated->state), new Bedrooms($propertyCreated->number_of_bedrooms), new City($propertyCreated->city), new ExtraDetails($propertyCreated->extra_details), new Bathrooms($propertyCreated->number_of_bathrooms), new Size($propertyCreated->size), new Coordinates($propertyCreated->latitude, $propertyCreated->longitude));
    }
}
