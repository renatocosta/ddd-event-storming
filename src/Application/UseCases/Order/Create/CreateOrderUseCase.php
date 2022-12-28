<?php

namespace Application\UseCases\Order\Create;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\Order;
use Domain\Model\Order\State\OrderTransition;
use Ramsey\Uuid\Uuid;
use Ddd\BnbSchemaRegistry\SchemaRegistry;
use Ddd\BnbSchemaRegistry\Schemas;

final class CreateOrderUseCase implements ICreateOrderUseCase
{

    public function __construct(public Order $order)
    {
    }

    public function execute(CreateOrderInput $input): void
    {

        $orderIdentifier = Guid::from(Uuid::uuid4());
        $orderNumber = new HumanCode();
        $payload = Payload::from($input->payload);

        $schemaRegistry = (new SchemaRegistry($payload->asArray()))
            ->readOf(Schemas::ORDER_CREATED_V1)
            ->withException()
            ->build();

        $order = $this->order->of($orderIdentifier, $input->customerId, $input->propertyId, $orderNumber, Payload::from($schemaRegistry->validated()));
        $orderTransition = new OrderTransition($order);

        $this->order
            ->fromState($orderTransition->nextState());
        $this->order->create();
    }
}
