<?php

namespace Application\EventHandlers\User;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\User\Events\CustomerAndPaymentEnrolled;
use Domain\Model\User\UnableToHandleUser;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Stripe\BaseStripeClient;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Exception\OAuth\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Throwable;

final class EnrollCustomerAndPaymentEventHandler implements DomainEventHandler
{

    public function __construct(private Order $order, private IOrderRepository $orderRepository, private BaseStripeClient $stripeClient)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $user = $domainEvent->entity;
        $payload = $this->order->payload();
        $payment = $payload->asArray()['event_data']['customer']['payment'];

        try {

            $stripeApiCustomer = $this->stripeClient->customers->create([
                'name' => $user->name(),
                'email' => $user->email(),
            ]);

            $this->stripeClient->paymentMethods->attach(
                $payment['payment_method_token'],
                ['customer' => $stripeApiCustomer->id]
            );
            $this->order->payload()->addAList([
                'event_data' =>
                [
                    'customer' =>
                    [
                        'payment' =>
                        ['customer_token' => $stripeApiCustomer->id]
                    ]
                ]
            ]);
        } catch (CardException | RateLimitException | InvalidRequestException | AuthenticationException | ApiConnectionException | ApiErrorException $e) {
            throw new UnableToHandleUser('There is something wrong with your credit card info, please check again.');
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }

        //Remove sensitive data
        $this->orderRepository->update($this->order);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof CustomerAndPaymentEnrolled;
    }
}
