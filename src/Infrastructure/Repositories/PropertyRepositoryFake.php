<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Domain\Model\Property\IPropertyRepository;
use Domain\Model\Property\Property;

final class PropertyRepositoryFake implements IPropertyRepository
{

    public function getById(int $id): Property
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function create(object $customer): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
