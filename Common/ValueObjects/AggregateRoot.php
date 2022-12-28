<?php

namespace Common\ValueObjects;

use Common\Application\Event\Bus\DomainEventBus;
use Common\Application\Event\Eventable;

abstract class AggregateRoot
{

    /**
     * @var DomainEventBus
     */
    protected ?DomainEventBus $domainEventBus;

    public function __construct(DomainEventBus $domainEventBus = null)
    {
        $this->domainEventBus = $domainEventBus;
    }

    protected function raise(Eventable $event): void
    {
        $this->domainEventBus->publish($event);
    }
}
