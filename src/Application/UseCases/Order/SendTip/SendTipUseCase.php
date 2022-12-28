<?php

namespace Application\UseCases\Order\SendTip;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntityNotFound;
use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infrastructure\Proxy\ProxyedBnB;

final class SendTipUseCase implements ISendTipUseCase
{

    public function __construct(public Order $order, public IOrderRepository $orderRepository, private ProxyedBnB $proxy)
    {
    }

    public function execute(SendTipInput $input): void
    {

        $order = $this->orderRepository->getByProjectAndCustomerId($input->projectId, $input->customerId);

        if ($order instanceof OrderEntityNotFound) throw new ModelNotFoundException(sprintf(UnableToHandleOrders::PROJECT_NOT_FOUND, $input->projectId));

        $this->order->withProjectId(Guid::from($order->getIdentifier()->id), $order->getStatus(), $order->orderNumber(), $order->payload(), $order->mobileNumber(), $input->projectId);
        $this->order->sendTip($input->amount, $input->currency);
    }

    public function proxy(): ProxyedBnB
    {
        return $this->proxy;
    }
}
