<?php

namespace Application\EventHandlers\Order;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\TipSent;
use Infrastructure\Proxy\ProxyedBnB;

final class SendTipEventHandler implements DomainEventHandler
{

    public function __construct(public ProxyedBnB $proxy)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $this->proxy->call('api/v1/ddd/sendTip', [
            'project_id' => $order->getProjectId(),
            'amount' => $order->getAmount(),
            'currency' => $order->getCurrency()
        ]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof TipSent;
    }
}
