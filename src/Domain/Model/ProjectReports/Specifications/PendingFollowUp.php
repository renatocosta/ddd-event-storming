<?php

namespace Domain\Model\ProjectReports\Specifications;

use Common\Specification\CompositeSpecification;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Support\Arr;

final class PendingFollowUp extends CompositeSpecification
{

    public function __construct(private IProjectReportsRepository $projectRepository)
    {
    }

    /**
     * @param array $params
     */
    public function isSatisfiedBy($params): bool
    {
        $resultSet = $this->projectRepository->getAll(
            ['status'],
            $params['order_id']
        )['data'];

        if (count($resultSet) == 0) return true;

        $matchAcceptedOffer = Arr::first($resultSet, function ($followUp, $key) {
            return $followUp['status'] == ProjectReportsStatus::STATUS[ProjectReportsStatus::ORDER_ACCEPTED];
        });

        return !$matchAcceptedOffer;
    }
}
