<?php

namespace Application\UseCases\Order\AssignProject;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;

final class AssignProjectUseCase implements IAssignProjectUseCase
{

    public function __construct(public Order $order, public IOrderRepository $orderRepository)
    {
    }

    public function execute(AssignProjectInput $input): void
    {

        $order = $this->orderRepository->get(Guid::from($input->orderId));
        $this->order->withProjectId(Guid::from($order->getIdentifier()->id), $order->getStatus(), $order->orderNumber(),  $order->payload(), $order->mobileNumber(), $input->projectId);
        $this->order->assignProject($input->projectId);
    }
}
