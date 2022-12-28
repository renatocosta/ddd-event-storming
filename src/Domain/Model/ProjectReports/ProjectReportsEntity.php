<?php

namespace Domain\Model\ProjectReports;

use Common\ValueObjects\AggregateRoot;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusReplicated;

final class ProjectReportsEntity extends AggregateRoot implements ProjectReports
{

    private ProjectReportsStatus $status;

    private Identified $identifier;

    private HumanCode $orderNumber;

    private Mobile $mobile;

    private Payload $payload;

    public function match(ProjectReportsStatus $incomingStatus): self
    {
        if ($this->status != $incomingStatus) {
            throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::STATUS_TRANSITION_VIOLATION, $this->status->previous(), $incomingStatus);
        }

        $this->status = $incomingStatus;

        return $this;
    }

    public function change(): void
    {
        $this->raise(new ProjectReportsStatusChanged($this));
    }

    public function shouldMapRelationships(): bool
    {
        return $this->getStatus() == ProjectReportsStatus::ORDER_ACCEPTED;
    }

    public function getStatus(): ProjectReportsStatus
    {
        return $this->status;
    }

    public function payload(): Payload
    {
        return $this->payload;
    }

    public function orderNumber(): HumanCode
    {
        return $this->orderNumber;
    }

    public function mobile(): Mobile
    {
        return $this->mobile;
    }

    public function wasPreviouslyReported(): bool
    {
        return !$this instanceof ProjectReportsEntityNotFound;
    }

    public function canBeReplicated(): self
    {
        if (!in_array($this->status, ProjectReportsStatus::REPLICATION_STATUS_ALLOWED)) throw UnableToHandleProjectReports::dueTo(UnableToHandleProjectReports::MISMATCH_REPLICATION_STATUS, $this->status);
        return $this;
    }

    public function replicate(): void
    {
        $this->raise(new ProjectReportsStatusReplicated($this));
    }

    public function getIdentifier(): Identified
    {
        return $this->identifier;
    }

    public function fromOrderAndStatus(HumanCode $orderNumber, ProjectReportsStatus $status): self
    {
        $this->orderNumber = $orderNumber;
        $this->status = $status;
        return $this;
    }

    public function from(Identified $identifier, HumanCode $orderNumber, ProjectReportsStatus $nextStatus, Payload $payload, Mobile $mobile): self
    {
        $this->identifier = $identifier;
        $this->orderNumber = $orderNumber;
        $this->status = $nextStatus;
        $this->payload = $payload;
        $this->mobile = $mobile;
        return $this;
    }

    public function of(Identified $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }
}
