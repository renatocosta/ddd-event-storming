<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class ProjectCanceledState extends ProjectReportsState
{
    protected string $fromStatus = ProjectReportsStatus::PROJECT_CANCELLED;
}
