<?php

namespace Application\UseCases\ProjectReports\Queries;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Exception;

final class GetProjectReportsQuery implements IGetProjectReportsQuery
{

    public function __construct(private ProjectReports $projectReports, private IProjectReportsRepository $projectReportsRepository)
    {
    }

    public function execute(string $orderNumber, int $status): ProjectReports
    {

        try {
            $project = $this->projectReports->fromOrderAndStatus(new HumanCode($orderNumber), ProjectReportsStatus::fromId($status));
        } catch (Exception $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }
        return $this->projectReportsRepository->getBy($project->orderNumber(), $project->getStatus());
    }
}
