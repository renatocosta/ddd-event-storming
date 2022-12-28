<?php

namespace Application\UseCases\ProjectReports\Queries;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsEntityNotFound;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetProjectReportsStateQuery implements IGetProjectReportsStateQuery
{

    public function __construct(private ProjectReports $projectReports, private IProjectReportsRepository $projectReportsRepository)
    {
    }

    public function execute(string $orderNumber): array
    {

        try {
            $projectReports = $this->projectReports->fromOrderAndStatus(new HumanCode($orderNumber), new ProjectReportsStatus(ProjectReportsStatus::CONFIRMED));
        } catch (Exception $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }

        $projectReportsState = $this->projectReportsRepository->getStateByOrderNumber($projectReports->orderNumber());

        if ($projectReportsState instanceof ProjectReportsEntityNotFound) throw new ModelNotFoundException();

        $cancellationReason = isset($projectReportsState->payload()->asArray()['event_data']['project']['cancellation_reason']) ? $projectReportsState->payload()->asArray()['event_data']['project']['cancellation_reason'] : false;
        return ['state' => $projectReportsState->getStatus()->getId(), 'cancellation_reason' => $cancellationReason];
    }
}
