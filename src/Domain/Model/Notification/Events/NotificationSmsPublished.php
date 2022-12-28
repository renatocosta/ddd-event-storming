<?php

namespace Domain\Model\Notification\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\Notification\Notification;

class NotificationSmsPublished extends AbstractEvent
{

    public function __construct(public Notification $entity)
    {
        parent::__construct($entity);
    }
}
