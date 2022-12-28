<?php

namespace Domain\Model\Order\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\Order\Order;

class RatingSent extends AbstractEvent
{

    public function __construct(public Order $entity)
    {
        parent::__construct($entity);
    }
}
