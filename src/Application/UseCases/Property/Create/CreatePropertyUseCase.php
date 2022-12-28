<?php

namespace Application\UseCases\Property\Create;

use Application\UseCases\Property\Create\CreatePropertyInput;
use Application\UseCases\Property\Create\ICreatePropertyUseCase;
use Common\ValueObjects\Geography\Address;
use Common\ValueObjects\Geography\City;
use Common\ValueObjects\Geography\State;
use Common\ValueObjects\Geography\Zipcode;
use Common\ValueObjects\Misc\Bathrooms;
use Common\ValueObjects\Misc\Bedrooms;
use Common\ValueObjects\Misc\Coordinates;
use Common\ValueObjects\Misc\ExtraDetails;
use Common\ValueObjects\Misc\Size;
use Domain\Model\Property\Property;

final class CreatePropertyUseCase implements ICreatePropertyUseCase
{

    public function __construct(public Property $property)
    {
    }

    public function execute(CreatePropertyInput $input): void
    {
        $this->property
            ->from($input->customerId, new Address($input->address), new Zipcode($input->zipcode), new State($input->state), new Bedrooms($input->bedrooms), new City($input->city), new ExtraDetails($input->extraDetails), new Bathrooms($input->bathrooms), new Size($input->size), new Coordinates($input->lat, $input->long))
            ->create();
    }
}
