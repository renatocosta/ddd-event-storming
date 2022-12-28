<?php

namespace Application\UseCases\Order\ReviewProject;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;

final class ReviewProjectUseCase implements IReviewProjectUseCase
{

    public function __construct(public Order $order, public IOrderRepository $orderRepository)
    {
    }

    public function execute(ReviewProjectInput $input): void
    {

        $order = $this->orderRepository->get(Guid::from($input->orderId));
        $this->order->withProjectId(Guid::from($order->getIdentifier()->id), $order->getStatus(), $order->orderNumber(), $order->payload(), $order->mobileNumber(), $order->getProjectId());
        $this->order->markAsReviewed();
    }
}
