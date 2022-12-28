<?php

namespace Infrastructure\Framework\Providers;

use Common\Application\Event\Bus\DomainEventBus;
use Common\Application\Event\DomainEventHandler;
use Faker\Provider\Base;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Infrastructure\Stripe\StripeFake;
use Mockery;

class CommonFakerProvider extends Base
{

    public function eventBus(DomainEventHandler ...$domainEventsHandler): DomainEventBus
    {
        $domainEventBus = new DomainEventBus();
        $domainEventBus->subscribers(...$domainEventsHandler);
        return $domainEventBus;
    }

    public function stripe()
    {
        $mock = new StripeFake();
        return $mock;
    }

    public function outBoxIntegrationRepository()
    {
        $mock = Mockery::mock(OutboxIntegrationEventsRepository::class);
        $mock->shouldReceive('readUnprocessed')->andReturn(null);
        $mock->shouldReceive('create');
        $mock->shouldReceive('updateAsProcessed');

        return $mock;
    }
}
