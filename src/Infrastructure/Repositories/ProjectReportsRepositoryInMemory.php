<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Contracts\Cache\Repository as Cache;

final class ProjectReportsRepositoryInMemory implements IProjectReportsRepository
{

    public function __construct(private IProjectReportsRepository $projectReportsRepository, private Cache $cache)
    {
    }

    public function getAll(array $filter = ['id', 'status'], string $id = null): array
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function get(Identified $identifier, array $filter = ['id', 'order_id', 'order_number', 'status', 'payload']): object
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getBy(HumanCode $orderNumber, ProjectReportsStatus $status, array $filter = ['id', 'order_number', 'status', 'payload']): ProjectReports
    {

        $cacheKey = sprintf(CacheUtils::PROJECT_REPORTS_KEY, $orderNumber, $status->getId());

        return $this->cache->remember($cacheKey, CacheUtils::TTL, function () use ($orderNumber, $status) {
            return $this->projectReportsRepository->getBy($orderNumber, $status);
        });
    }

    public function getStateByOrderNumber(HumanCode $orderNumber): ProjectReports
    {
        $cacheKey = sprintf(CacheUtils::PROJECT_CURRENT_STATUS, $orderNumber);

        return $this->cache->remember($cacheKey, CacheUtils::TTL, function () use ($orderNumber) {
            return $this->projectReportsRepository->getStateByOrderNumber($orderNumber);
        });
    }

    public function getByStatus(Identified $identifier, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload']): ProjectReports
    {
        $cacheKey = sprintf(CacheUtils::PROJECT_REPORTED, $identifier->getId());
        return $this->cache->remember($cacheKey, CacheUtils::TTL, function () use ($identifier, $status) {
            return $this->projectReportsRepository->getByStatus($identifier, $status);
        });
    }

    public function create(object $item): void
    {

        //Invalidate the most recent state within unit of work (ACID)
        $cacheKey = sprintf(CacheUtils::PROJECT_CURRENT_STATUS, (string) $item->orderNumber());
        $this->cache->forget($cacheKey);

        $cacheKey = sprintf(CacheUtils::PROJECT_REPORTS_KEY, $item->orderNumber(), $item->getStatus()->getId());
        $this->cache->forget($cacheKey);

        $this->projectReportsRepository->create($item);
    }

    public function update(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function remove(object $entity): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }
}
