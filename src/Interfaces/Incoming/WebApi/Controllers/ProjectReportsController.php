<?php

namespace Interfaces\Incoming\WebApi\Controllers;

use App\Http\Controllers\Controller;
use Application\Orchestration\ChangeProjectReportsOrchestrator;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsInput;
use Application\UseCases\ProjectReports\Queries\IGetProjectReportsFollowUpQuery;
use Application\UseCases\ProjectReports\Queries\IGetProjectReportsQuery;
use Application\UseCases\ProjectReports\Queries\IGetProjectReportsShareQuery;
use Application\UseCases\ProjectReports\Queries\IGetProjectReportsStateQuery;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Framework\Transformers\BasicProjectReportsResource;
use Infrastructure\Framework\Transformers\ProjectReportsResource;
use Interfaces\Incoming\WebApi\Requests\ProjectReportedRequest;

class ProjectReportsController extends Controller
{
    public function fetchBy(string $orderNumber, int $status, IGetProjectReportsQuery $query)
    {
        return (new ProjectReportsResource($query->execute($orderNumber, $status)));
    }

    public function currentState(string $orderNumber, IGetProjectReportsStateQuery $query)
    {
        return new BasicProjectReportsResource($query->execute($orderNumber));
    }

    public function share(string $orderId, ProjectReportedRequest $projectReportedRequest, ChangeProjectReportsOrchestrator $changeProjectUseCase)
    {
        $changeProjectUseCase->execute(new ChangeProjectReportsInput($orderId, json_encode($projectReportedRequest->validated())));
        return response()->json([
            '_type'               => 'ProjectReports',
        ]);
    }

    public function reported(string $orderId, IGetProjectReportsShareQuery $query)
    {
        return (new ProjectReportsResource($query->execute($orderId, new ProjectReportsStatus(ProjectReportsStatus::PROJECT_FINISHED))));
    }

    public function fetchAll(IProjectReportsRepository $projectRepository)
    {
        return $projectRepository->getAll();
    }

    public function followUp(string $orderNumber, IGetProjectReportsFollowUpQuery $query)
    {
        return ProjectReportsResource::collection($query->execute($orderNumber));
    }
}
