<?php

namespace Domain\Model\Customer;

use Common\ValueObjects\Entity;
use Common\ValueObjects\Geography\Timezone;
use Common\ValueObjects\Identity\Identified;

final class CustomerOrderLocation extends Entity
{

    public function __construct(public Timezone $timezone, public Identified $orderId)
    {
    }

    public function timezone(): Timezone
    {
        return $this->timezone;
    }

    public function orderId(): Identified
    {
        return $this->orderId;
    }
}
