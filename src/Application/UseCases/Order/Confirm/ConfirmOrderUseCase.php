<?php

namespace Application\UseCases\Order\Confirm;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\State\OrderTransition;
use Ddd\BnbSchemaRegistry\Schemas;
use Ddd\BnbSchemaRegistry\SchemaRegistry;

final class ConfirmOrderUseCase implements IConfirmOrderUseCase
{

    public function __construct(public Order $order, public IOrderRepository $orderRepository)
    {
    }

    public function execute(ConfirmOrderInput $input): void
    {
        $order = $this->orderRepository->get(Guid::from($input->id));

        $payload = $order->payload()
            ->replaceWith('event_type', 'OrderConfirmed');

        $this->order->fromExisting(Guid::from($order->getIdentifier()->id), $order->getCustomerId(), $order->getPropertyId(), $order->getStatus(), $order->orderNumber(), $payload, $order->mobileNumber())
            ->withSmsVerificationCode(new SmsVerificationCode($input->smsVerificationCode));

        (new SchemaRegistry($this->order->payload()->asArray()))
            ->readOf(Schemas::ORDER_CONFIRMED_V1)
            ->withException()
            ->build();

        $orderTransition = new OrderTransition($this->order);
        $this->order->fromState($orderTransition->nextState());
        $this->order->confirm();
    }
}
