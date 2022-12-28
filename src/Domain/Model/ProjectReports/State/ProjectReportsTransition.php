<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;

final class ProjectReportsTransition
{

    private ProjectReportsStatus $status;

    public function __construct(private ProjectReports $project, ProjectReportsStatus $incomingStatus)
    {
        if ($project->wasPreviouslyReported()) {
            $this->status = $this->fromExisting($incomingStatus, $project);
            return;
        }

        $this->status = $this->fromBeginning($incomingStatus);
    }

    private function from(ProjectReports $project, ProjectReportsStatus $incomingStatus): ProjectReportsStatus
    {
        return match ((string) $project->getStatus()) {
            ProjectReportsStatus::CONFIRMED => $this->to(new ProjectConfirmedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED => $this->to(new CreditCardAuthFailedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED => $this->to(new CreditCardAuthSuccededState(), $incomingStatus, $project),
            ProjectReportsStatus::ORDER_ACCEPTED => $this->to(new OrderAcceptedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_STARTED => $this->to(new ProjectStartedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_FINISHED => $this->to(new ProjectFinishedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_REPORTED => $this->to(new ProjectReportedState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_CANCELLED => (new ProjectCanceledState())->cancelProject(),
            ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED => $this->to(new ProjectPaymentSucceededState(), $incomingStatus, $project),
            ProjectReportsStatus::PROJECT_PAYMENT_FAILED => $this->to(new ProjectPaymentFailedState(), $incomingStatus, $project),
            default => throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::UNKNOWN_PROJECT_STATE_ON_REPORTING, $project->getIdentifier()->id),
        };
    }

    private function to(ProjectReportsStateable $state, ProjectReportsStatus $incomingStatus, ProjectReports $project): ProjectReportsStatus
    {
        return match ((string) $incomingStatus) {
            ProjectReportsStatus::ORDER_ACCEPTED => $state->acceptOrder(),
            ProjectReportsStatus::PROJECT_STARTED => $state->startProject(),
            ProjectReportsStatus::PROJECT_CANCELLED => $state->cancelProject(),
            ProjectReportsStatus::PROJECT_FINISHED => $state->finishProject(),
            ProjectReportsStatus::PROJECT_REPORTED => $state->reportProject(),
            ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED => $state->finishPayment(),
            ProjectReportsStatus::PROJECT_PAYMENT_FAILED => $state->markPaymentAsFailed(),
            ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED => $state->markCreditCardAuthAsFailed(),
            ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED => $state->markCreditCardAuthAsSucceded(),
            default => throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::MISMATCH_NEXT_PROJECT_STATUS, $project->getStatus()),
        };
    }

    private function fromExisting(ProjectReportsStatus $projectStatus, ProjectReports $project): ProjectReportsStatus
    {

        $hasNoTransitions = [ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_CLEANING_COMING_UP_NOTIFIED], ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_CLEANER_UPDATED_THE_CLEANING]];

        if (in_array($projectStatus->getId(), $hasNoTransitions)) {
            return $projectStatus;
        }

        return $this->from($project, $projectStatus);
    }

    private function fromBeginning(ProjectReportsStatus $projectStatus): ProjectReportsStatus
    {
        $acceptableDefaultStatuses = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED, ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED, ProjectReportsStatus::PROJECT_CANCELLED];

        if (!in_array($projectStatus, $acceptableDefaultStatuses)) {
            throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::MISMATCH_INITAL_PROJECT_STATUS, $projectStatus);
        }

        return $projectStatus;
    }

    public function nextStatus(): ProjectReportsStatus
    {
        return $this->status;
    }
}
