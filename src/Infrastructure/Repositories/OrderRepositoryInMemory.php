<?php

namespace Infrastructure\Repositories;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Illuminate\Contracts\Cache\Repository as Cache;

final class OrderRepositoryInMemory implements IOrderRepository
{

    public function __construct(private IOrderRepository $orderRepository, private Cache $cache)
    {
    }

    public function getAll(array $filter = ['id', 'customer_id', 'status', 'order_number', 'payload', 'project_id', 'reviewed', 'created_at'], string $orderId = null): array
    {
        return $this->orderRepository->getAll($filter, $orderId);
    }

    public function getById(Identified $identifier): Order
    {
        return $this->orderRepository->getById($identifier);
    }

    public function get(Identified $identifier, array $filter = []): object
    {
        return $this->orderRepository->get($identifier, $filter);
    }

    public function getBy(HumanCode $orderNumber, array $filter = []): Order
    {
        $cacheKey = sprintf(CacheUtils::ORDER_KEY, $orderNumber);

        return $this->cache->remember($cacheKey, CacheUtils::TTL, function () use ($orderNumber) {
            return $this->orderRepository->getBy($orderNumber);
        });
    }

    public function getByProjectAndCustomerId(int $projectId, int $customerId, array $filter = ['id', 'order_number', 'status', 'payload']): Order
    {
        return $this->orderRepository->getByProjectAndCustomerId($projectId, $customerId, $filter);
    }

    public function getByAddressAndNumber(string $address, string $number = null): Order
    {
        return $this->orderRepository->getByAddressAndNumber($address, $number);
    }

    public function create(object $item): void
    {
        $cacheKey = sprintf(CacheUtils::ORDER_KEY, (string) $item->orderNumber());
        $this->cache->forget($cacheKey);

        $this->orderRepository->create($item);
    }

    public function update(object $order): void
    {
        $cacheKey = sprintf(CacheUtils::ORDER_KEY, (string) $order->orderNumber());
        $this->cache->forget($cacheKey);

        $this->orderRepository->update($order);
    }

    public function remove(object $entity): void
    {
        $this->orderRepository->remove($entity);
    }
}
