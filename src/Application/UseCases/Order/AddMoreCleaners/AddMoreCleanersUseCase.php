<?php

namespace Application\UseCases\Order\AddMoreCleaners;

use Common\Specification\Specification;
use Common\ValueObjects\Identity\Guid;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\State\OrderTransition;
use Domain\Model\Order\UnableToHandleOrders;
use Ddd\BnbSchemaRegistry\SchemaRegistry;
use Ddd\BnbSchemaRegistry\Schemas;

final class AddMoreCleanersUseCase implements IAddMoreCleanersUseCase
{

    public function __construct(public Order $order, public IOrderRepository $repository, public Specification $pendingFollowUp)
    {
    }

    public function execute(AddMoreCleanersInput $input): void
    {

        $order = $this->repository->get(Guid::from($input->orderId));
        $payload = $order->payload();

        $payload = $payload->addAList([
            'current_status' => $order->getStatus()->getId(),
            'event_type' => 'CustomerAddedMoreCleaners',
            'new_cleaners' => $input->cleaners,
            'event_data' =>
            [
                'cleaners' => array_merge($input->cleaners, $order->payload()->asArray()['event_data']['cleaners']),
                'new_cleaners' => $input->cleaners
            ]
        ]);

        (new SchemaRegistry($payload->asArray()))
            ->readOf(Schemas::CUSTOMER_ADDED_MORE_CLEANERS_V1)
            ->withException()
            ->build();

        $orderTransition = new OrderTransition($order);

        $this->order->fromExisting(Guid::from($order->getIdentifier()->id), $order->getCustomerId(), $order->getPropertyId(), $order->getStatus(), $order->orderNumber(), $payload, $order->mobileNumber(), $order->getProjectId());

        $this->order
            ->fromState($orderTransition->nextState());

        if (!$this->pendingFollowUp->isSatisfiedBy(['order_id' => $input->orderId])) {
            throw new UnableToHandleOrders(sprintf('The order %s is unable to add more cleaners', $input->orderId));
        }

        $this->order->update();
    }
}
