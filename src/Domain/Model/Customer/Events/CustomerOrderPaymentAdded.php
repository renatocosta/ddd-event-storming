<?php

namespace Domain\Model\Customer\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\Customer\Customer;

class CustomerOrderPaymentAdded extends AbstractEvent
{

    public function __construct(public Customer $entity)
    {
        parent::__construct($entity);
    }
}
