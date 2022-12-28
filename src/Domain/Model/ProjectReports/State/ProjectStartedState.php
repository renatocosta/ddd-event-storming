<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class ProjectStartedState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_STARTED;

    public function finishProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_FINISHED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CANCELLED);
    }
}
