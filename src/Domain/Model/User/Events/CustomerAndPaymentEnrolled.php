<?php

namespace Domain\Model\User\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\User\User;

class CustomerAndPaymentEnrolled extends AbstractEvent
{

    public function __construct(public User $entity)
    {
        parent::__construct($entity);
    }
}
