<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\Order\Order;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Framework\Providers\ProjectReportsFakerProvider;

final class ProjectReportsRepositoryFake implements IProjectReportsRepository
{

    public function __construct(private ProjectReportsFakerProvider  $faker)
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

    public function getBy(HumanCode $orderNumber, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'order_number', 'status', 'payload']): ProjectReports
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByProjectAndUserId(int $projectId, string $userId, array $filter = ['id', 'order_number', 'status', 'payload']): Order
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getByStatus(Identified $identifier, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload']): ProjectReports
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getStateByOrderNumber(HumanCode $orderNumber): ProjectReports
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function create(object $item): void
    {
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
