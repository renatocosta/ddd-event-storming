<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\User\Events\PaymentMethodUpdated;
use Infrastructure\Proxy\ProxyedBnB;

final class PaymentMethodUpdatedEventHandler implements DomainEventHandler
{

    public function __construct(private ProxyedBnB $proxy)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $this->proxy->call('api/v1/ddd/updatePaymentMethod', [
            'payment_method_token' => $user->getPaymentMethodToken(),
            'customer_id' => $user->getCustomerId()
        ]);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof PaymentMethodUpdated;
    }
}
