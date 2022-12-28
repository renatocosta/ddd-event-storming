<?php

namespace Infrastructure\Repositories;

use BadMethodCallException;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\ProjectReportsEntityNotFound;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Framework\Entities\ProjectStatusReportsModel;

final class ProjectReportsRepository implements IProjectReportsRepository
{

    public const PAGINATE_ITEMS_PER_PAGE = 10;

    public function getAll(array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload', 'created_at'], string $orderId = null): array
    {
        return (new ProjectStatusReportsModel)
            ->on('mysql::read')
            ->select($filter)
            ->when($orderId, function ($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(self::PAGINATE_ITEMS_PER_PAGE)->toArray();
    }

    public function get(Identified $identifier, array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload']): object
    {
        $project = (new ProjectStatusReportsModel)
            ->on('mysql::read')
            ->select($filter)
            ->where('order_id', $identifier->getId())
            ->whereIn('status', ProjectReportsStatus::FOLLOW_UP_STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($project) {
            return (new ProjectReportsEntity())->from(Guid::from($project->order_id), new HumanCode($project->order_number), ProjectReportsStatus::fromId($project->status), new Payload($project->payload), new Mobile($project->mobile));
        }

        return new ProjectReportsEntityNotFound();
    }

    public function getStateByOrderNumber(HumanCode $orderNumber): ProjectReports
    {

        $project = (new ProjectStatusReportsModel)
            ->on('mysql::read')
            ->where('order_number', $orderNumber)
            ->whereIn('status', ProjectReportsStatus::FOLLOW_UP_STATUS_ACTIVE)
            ->select(['id', 'order_id', 'mobile', 'order_number', 'status', 'payload'])
            ->orderBy('status', 'desc')
            ->first();

        if ($project) {
            return (new ProjectReportsEntity())->from(Guid::from($project->order_id), new HumanCode($project->order_number), ProjectReportsStatus::fromId($project->status), new Payload($project->payload), new Mobile($project->mobile));
        }

        return new ProjectReportsEntityNotFound();
    }

    public function getByStatus(Identified $identifier, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload']): ProjectReports
    {
        $project = (new ProjectStatusReportsModel)
            ->on('mysql::read')
            ->select($filter)
            ->where('order_id', $identifier->getId())
            ->where('status', $status->getId())
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        return (new ProjectReportsEntity())->from(Guid::from($project->order_id), new HumanCode($project->order_number), ProjectReportsStatus::fromId($project->status), new Payload($project->payload), new Mobile($project->mobile));
    }

    public function getBy(HumanCode $orderNumber, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'mobile', 'order_number', 'status', 'payload']): ProjectReports
    {

        $project = (new ProjectStatusReportsModel)
            ->on('mysql::read')
            ->where('order_number', $orderNumber)
            ->when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('mobile', auth()->user()->mobile);
            })
            ->where('status', $status->getId())
            ->select($filter)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        return (new ProjectReportsEntity())->from(Guid::from($project->order_id), new HumanCode($project->order_number), ProjectReportsStatus::fromId($project->status), new Payload($project->payload), new Mobile($project->mobile));
    }

    public function create(object $item): void
    {
        (new ProjectStatusReportsModel)
            ->on('mysql::write')
            ->create(['order_id' => $item->getIdentifier()->id, 'order_number' => (string) $item->orderNumber(), 'mobile' => (string) $item->mobile(), 'status' => $item->getStatus()->getId(), 'payload' => (string) $item->payload()]);
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
