<?php

namespace Application\UseCases\Order\Queries;

interface IGetAddressAvailabilityQuery
{

    public function execute(string $address, string $unitNumber = null): bool;
}
