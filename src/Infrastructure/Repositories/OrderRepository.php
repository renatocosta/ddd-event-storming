<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderEntityNotFound;
use Domain\Model\Order\OrderStatus;
use Infrastructure\Framework\Entities\OrderModel;

final class OrderRepository implements IOrderRepository
{

    public const PAGINATE_ITEMS_PER_PAGE = 10;

    public function __construct(private OrderModel $orderModelWriter, private OrderModel $orderModelReader)
    {
    }

    public function getAll(array $filter = ['id', 'customer_id', 'status', 'order_number', 'payload', 'project_id', 'reviewed', 'created_at'], string $orderId = null): array
    {
        return $this->orderModelReader
            ->select($filter)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(self::PAGINATE_ITEMS_PER_PAGE)->toArray();
    }

    public function getById(Identified $identifier): Order
    {
        $order = $this->orderModelReader
            ->orderBy('created_at', 'desc')
            ->findOrFail((string) $identifier);

        $payload = new Payload($order->payload);
        $payloadUnserialzed = $payload->asArray();
        $mobile = is_null($order->user?->mobile) ? $payloadUnserialzed['event_data']['customer']['personal_information']['phone_number'] : $order->user->mobile;

        return (new OrderEntity())->fromExisting(Guid::from($order->id), $order->customer_id, $order->property_id, new OrderStatus($order->status), new HumanCode($order->order_number), $payload, new Mobile($mobile));
    }

    public function get(Identified $identifier, array $filter = []): object
    {

        $order = $this->orderModelReader
            ->orderBy('created_at', 'desc')
            ->findOrFail((string) $identifier);

        $payload = new Payload($order->payload);
        $payloadUnserialzed = $payload->asArray();

        $mobile = is_null($order->user?->mobile) ? $payloadUnserialzed['event_data']['customer']['personal_information']['phone_number'] : $order->user->mobile;
        $projectId = $order->project_id ?? 0;

        return (new OrderEntity())->fromExisting(Guid::from($order->id), $order->customer_id, $order->property_id, new OrderStatus($order->status), new HumanCode($order->order_number), $payload, new Mobile($mobile), $projectId);
    }

    public function getBy(HumanCode $orderNumber, array $filter = []): Order
    {
        $order = $this->orderModelReader
            ->where('order_number', $orderNumber)
            ->when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('customer_id', auth()->user()->customer_id);
            })
            ->orderBy('created_at', 'desc')
            ->firstOrFail();
        return (new OrderEntity())->fromExisting(Guid::from($order->id), $order->customer_id, $order->property_id, new OrderStatus($order->status), new HumanCode($order->order_number), new Payload($order->payload), new Mobile($order->customer->mobile), 0, $order->reviewed);
    }

    public function getByProjectAndCustomerId(int $projectId, int $customerId, array $filter = ['id', 'customer_id', 'order_number', 'status', 'payload']): Order
    {
        $order = $this->orderModelReader
            ->where('project_id', $projectId)
            ->where('customer_id', $customerId)
            ->first();

        if ($order) {
            return (new OrderEntity())->fromExisting(Guid::from($order->id), $order->customer_id, $order->property_id, new OrderStatus($order->status), new HumanCode($order->order_number), new Payload($order->payload), new Mobile($order->customer->mobile));
        }

        return new OrderEntityNotFound();
    }

    public function getByAddressAndNumber(string $address, string $number = null): Order
    {
        $order = $this->orderModelReader
            ->with(['property' => function ($query) use ($address, $number) {
                $query->where('address', $address);
                $query->when(!is_null($number), function ($query) use ($number) {
                    $query->where('extra_details', $number);
                });
            }])
            ->whereHas('property', function ($query) use ($address, $number) {
                $query->where('address', $address);
                $query->when(!is_null($number), function ($query) use ($number) {
                    $query->where('extra_details', $number);
                });
            })
            ->where('status', OrderStatus::CONFIRMED)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($order) {
            return (new OrderEntity())->fromExisting(Guid::from($order->id), $order->customer_id, $order->property_id, new OrderStatus($order->status), new HumanCode($order->order_number), new Payload($order->payload), new Mobile($order->customer->mobile), $order->project_id ?? 0);
        }

        return new OrderEntityNotFound();
    }

    public function create(object $item): void
    {

        $data = ['id' => $item->getIdentifier()->id, 'customer_id' => $item->getCustomerId(), 'property_id' => $item->getPropertyId(), 'order_number' => $item->orderNumber(), 'status' => $item->getStatus()->getId(), 'payload' => $item->payload()];

        if ($item->getProjectId() > 0) {
            $data['project_id'] = $item->getProjectId();
        }

        $this->orderModelWriter
            ->fill($data)->save();
    }

    public function update(object $order): void
    {
        $orderUpd = $this->orderModelWriter->find($order->getIdentifier()->id);

        if ($order->getProjectId() > 0) {
            $orderUpd->project_id = $order->getProjectId();
        }
        if (!empty($order->payload())) {
            $orderUpd->payload = (string) $order->payload();
        }

        if ($order->reviewed()) {
            $orderUpd->reviewed = $order->reviewed();
        }

        $orderUpd->save();
    }

    public function remove(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
