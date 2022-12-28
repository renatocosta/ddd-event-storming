<?php

namespace Domain\Services;

interface IPropertyAddressInUse
{
    /**
     * 
     * @param string $address
     * @param string $unitNumber
     */
    public function match(string $address, string $unitNumber = null): bool;
}
