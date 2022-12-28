<?php

namespace Domain\Model\Property\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\Property\Property;

class PropertyCreated extends AbstractEvent
{

    public function __construct(public Property $entity)
    {
        parent::__construct($entity);
    }
}
