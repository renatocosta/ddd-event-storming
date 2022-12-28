<?php

namespace Application\UseCases\ProjectReports\Queries;

use Common\ValueObjects\Identity\Guid;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Exception;

final class GetProjectReportsShareQuery implements IGetProjectReportsShareQuery
{

    public function __construct(private ProjectReports $projectReports, private IProjectReportsRepository $projectReportsRepository)
    {
    }

    public function execute(string $orderId, ProjectReportsStatus $status): ProjectReports
    {

        try {
            $projectReports = $this->projectReports->of(Guid::from($orderId));
        } catch (Exception $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }
        return $this->projectReportsRepository->getByStatus($projectReports->getIdentifier(), $status);
    }
}
