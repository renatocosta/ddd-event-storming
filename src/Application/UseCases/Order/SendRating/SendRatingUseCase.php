<?php

namespace Application\UseCases\Order\SendRating;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntityNotFound;
use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infrastructure\Proxy\ProxyedBnB;

final class SendRatingUseCase implements ISendRatingUseCase
{

    public function __construct(public Order $order, public IOrderRepository $orderRepository, public ProxyedBnB $proxy)
    {
    }

    public function execute(SendRatingInput $input): void
    {

        $order = $this->orderRepository->getByProjectAndCustomerId($input->projectId, $input->customerId);

        if ($order instanceof OrderEntityNotFound) throw new ModelNotFoundException(sprintf(UnableToHandleOrders::PROJECT_NOT_FOUND, $input->projectId));

        $this->order->withProjectId(Guid::from($order->getIdentifier()->id), $order->getStatus(), $order->orderNumber(), $order->payload(), $order->mobileNumber(), $input->projectId);
        $this->order->sendRating($input->rating, $input->review);
    }

    public function proxy(): ProxyedBnB
    {
        return $this->proxy;
    }
}
