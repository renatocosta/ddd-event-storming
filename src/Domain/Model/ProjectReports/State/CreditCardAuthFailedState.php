<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;

class CreditCardAuthFailedState extends ProjectReportsState
{

    protected string $fromStatus = ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED;

    public function markCreditCardAuthAsFailed(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED);
    }

    public function markCreditCardAuthAsSucceded(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        return new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CANCELLED);
    }
}
