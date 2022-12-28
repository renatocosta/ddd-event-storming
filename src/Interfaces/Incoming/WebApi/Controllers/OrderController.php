<?php

namespace Interfaces\Incoming\WebApi\Controllers;

use App\Http\Controllers\Controller;
use Application\Orchestration\ConfirmOrderOrchestrator;
use Application\Orchestration\CreateOrderOrchestrator;
use Application\UseCases\Order\AddMoreCleaners\AddMoreCleanersInput;
use Application\UseCases\Order\AddMoreCleaners\IAddMoreCleanersUseCase;
use Application\UseCases\Order\Confirm\ConfirmOrderInput;
use Application\UseCases\Order\Queries\IGetAddressAvailabilityQuery;
use Application\UseCases\Order\Queries\IGetOrderQuery;
use Application\UseCases\Order\SendRating\ISendRatingUseCase;
use Application\UseCases\Order\SendRating\SendRatingInput;
use Application\UseCases\Order\SendTip\ISendTipUseCase;
use Application\UseCases\Order\SendTip\SendTipInput;
use Domain\Model\Order\IOrderRepository;
use Infrastructure\Framework\Transformers\BasicOrderResource;
use Infrastructure\Framework\Transformers\OrderAddressAvailabilityResource;
use Infrastructure\Framework\Transformers\OrderResource;
use Interfaces\Incoming\WebApi\Requests\OrderCreatedRequest;
use Interfaces\Incoming\WebApi\Requests\OrderUpdatedRequest;
use Symfony\Component\HttpFoundation\Response;
use Interfaces\Incoming\WebApi\Requests\SendRatingRequest;
use Interfaces\Incoming\WebApi\Requests\SendTipRequest;

class OrderController extends Controller
{

    public function create(OrderCreatedRequest $request, CreateOrderOrchestrator $createOrderUseCase)
    {
        $createOrderUseCase->execute($request->validated());
        return (new BasicOrderResource($createOrderUseCase->order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function confirm(string $orderId, int $smsVerificationCode, ConfirmOrderOrchestrator $confirmOrderUseCase)
    {
        $confirmOrderUseCase->execute(new ConfirmOrderInput($orderId, $smsVerificationCode));
        return (new BasicOrderResource($confirmOrderUseCase->order))
            ->additional(['data' => ['token' => $confirmOrderUseCase->user->token(), 'order_number' => (string) $confirmOrderUseCase->order->orderNumber(), 'user_id' => $confirmOrderUseCase->user->userIdentified()->id]]);
    }

    public function fetchBy(string $orderNumber, IGetOrderQuery $query)
    {
        return (new OrderResource($query->execute($orderNumber)));
    }

    public function fetchAll(IOrderRepository $orderRepository)
    {
        return $orderRepository->getAll();
    }

    public function sendRating(SendRatingRequest $request, ISendRatingUseCase $sendRatingUseCase)
    {
        $sendRatingUseCase->execute(new SendRatingInput($request->get('project_id'), $request->get('rating'), $request->get('review'), auth()->user()->customer_id));
        return response('', $sendRatingUseCase->proxy()->response()->getStatusCode());
    }

    public function sendTip(SendTipRequest $request, ISendTipUseCase $sendTipUseCase)
    {
        $sendTipUseCase->execute(new SendTipInput($request->get('project_id'), $request->get('amount'), $request->get('currency'), auth()->user()->customer_id));
        return response('', $sendTipUseCase->proxy()->response()->getStatusCode());
    }

    public function checkAddressAvailability(string $address, string $unitNumber = null, IGetAddressAvailabilityQuery $query)
    {
        $inUse = $query->execute($address, $unitNumber);
        return (new OrderAddressAvailabilityResource($query))
            ->additional(['data' => ['in_use' => $inUse]]);
    }

    public function addMoreCleaners(OrderUpdatedRequest $request, IAddMoreCleanersUseCase $addMoreCleanersUseCase)
    {
        $addMoreCleanersUseCase->execute(new AddMoreCleanersInput($request->get('order_id'), $request->get('cleaners')));
    }
}
