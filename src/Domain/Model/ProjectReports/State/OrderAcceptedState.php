<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class OrderAcceptedState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::ORDER_ACCEPTED;

    public function startProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_STARTED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CANCELLED);
    }

    public function markCreditCardAuthAsSucceded(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED);
    }

    public function markCreditCardAuthAsFailed(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED);
    }

    public function acceptOrder(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::ORDER_ACCEPTED);
    }
}
