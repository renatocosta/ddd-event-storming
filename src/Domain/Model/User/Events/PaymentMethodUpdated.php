<?php

namespace Domain\Model\User\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\User\User;

class PaymentMethodUpdated extends AbstractEvent
{

    public function __construct(public User $entity)
    {
        parent::__construct($entity);
    }
}
