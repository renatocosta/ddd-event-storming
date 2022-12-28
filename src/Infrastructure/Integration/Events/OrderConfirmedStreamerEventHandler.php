<?php

namespace Infrastructure\Integration\Events;

use Common\Application\Event\AbstractEvent;
use Common\Infrastructure\Event\IntegrationEventHandler;
use Domain\Model\Order\Events\OrderConfirmed;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Ddd\BnbSchemaRegistry\SchemaRegistry;

final class OrderConfirmedStreamerEventHandler implements IntegrationEventHandler
{

    public function __construct(private Producer $kafkaProducer)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $payload = $order->payload()->asArray();

        $schemaRegistry = (new SchemaRegistry($payload))
            ->readFrom($payload['event_type'])
            ->withException()
            ->build();

        $payloadFiltered = $schemaRegistry->safe()->except(['event_data.customer.user_id']);

        $this->kafkaProducer::sendMessage("ddd.orders.confirmed", ['payload' => $payloadFiltered, 'correlationId' => $order->getIdentifier()->id]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof OrderConfirmed;
    }
}
