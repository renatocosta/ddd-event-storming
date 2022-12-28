<?php

namespace Domain\Model\Property;

interface IPropertyRepository
{

    public function getById(int $id): Property;

    public function create(object $entity): void;
}
