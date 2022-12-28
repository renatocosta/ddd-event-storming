<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;

abstract class ProjectReportsState implements ProjectReportsStateable
{

    protected string $fromStatus;

    public function confirm(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::CONFIRMED);
    }

    public function markCreditCardAuthAsFailed(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED);
    }

    public function markCreditCardAuthAsSucceded(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED);
    }

    public function acceptOrder(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::ORDER_ACCEPTED);
    }

    public function startProject(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_STARTED);
    }

    public function finishProject(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_FINISHED);
    }

    public function reportProject(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_REPORTED);
    }

    public function cancelProject(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_CANCELLED);
    }

    public function finishPayment(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED);
    }

    public function markPaymentAsFailed(): ProjectReportsStatus
    {
        throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->fromStatus, ProjectReportsStatus::PROJECT_PAYMENT_FAILED);
    }
}
