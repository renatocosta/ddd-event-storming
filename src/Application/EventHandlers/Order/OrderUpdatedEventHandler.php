<?php

namespace Application\EventHandlers\Order;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\OrderUpdated;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\OrderStatus;
use Ddd\BnbEnqueueClient\Facades\Producer;

final class OrderUpdatedEventHandler implements DomainEventHandler
{

    public function __construct(
        private IOrderRepository $orderRepository,
        private Producer $kafkaProducer
    ) {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $payload = $order->payload()->asArray();

        if ($payload['current_status'] == OrderStatus::CONFIRMED) {
            $this->orderRepository->create($order);
        } else {
            $this->orderRepository->update($order);
        }

        $this->kafkaProducer::sendMessage("ddd.orders.statuschanges", [
            'payload' =>
            ['event_type' => 'CustomerAddedMoreCleaners', 'entity_type' => 'Order', 'version' => '1', 'event_data' => [
                'order_id' => $order->getIdentifier()->id,
                'cleaners' => $payload['event_data']['cleaners'],
            ]],
            'correlationId' => $order->getIdentifier()->id
        ]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof OrderUpdated;
    }
}
