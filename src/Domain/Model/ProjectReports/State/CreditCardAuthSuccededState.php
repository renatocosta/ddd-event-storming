<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class CreditCardAuthSuccededState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED;

    public function acceptOrder(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::ORDER_ACCEPTED);
    }

    public function startProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_STARTED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CANCELLED);
    }
}
