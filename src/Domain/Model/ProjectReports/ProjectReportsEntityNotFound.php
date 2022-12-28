<?php

namespace Domain\Model\ProjectReports;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;

final class ProjectReportsEntityNotFound implements ProjectReports
{

    private ProjectReportsStatus $status;

    private Identified $identifier;

    private HumanCode $orderNumber;

    private Payload $payload;

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

    public function match(ProjectReportsStatus $incomingStatus): self
    {
        return $this;
    }

    public function change(): void
    {
    }

    public function shouldMapRelationships(): bool
    {
        return false;
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
        return $this;
    }

    public function replicate(): void
    {
    }

    public function getIdentifier(): Identified
    {
        return $this->identifier;
    }

    public function from(Identified $identifier, HumanCode $orderNumber, ProjectReportsStatus $status, Payload $payload, Mobile $mobile): self
    {
        return $this;
    }

    public function fromOrderAndStatus(HumanCode $orderNumber, ProjectReportsStatus $status): self
    {
        return $this;
    }

    public function of(Identified $identifier): self
    {
        return $this;
    }
}
