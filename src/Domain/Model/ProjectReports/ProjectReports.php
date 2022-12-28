<?php

namespace Domain\Model\ProjectReports;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;

interface ProjectReports
{

    public function getStatus(): ProjectReportsStatus;

    public function payload(): Payload;

    public function orderNumber(): HumanCode;

    public function mobile(): Mobile;

    public function wasPreviouslyReported(): bool;

    public function canBeReplicated(): self;

    public function replicate(): void;

    public function match(ProjectReportsStatus $incomingStatus): self;

    public function change(): void;

    public function shouldMapRelationships(): bool;

    public function from(Identified $identifier, HumanCode $orderNumber, ProjectReportsStatus $nextStatus, Payload $payload, Mobile $mobile): self;

    public function fromOrderAndStatus(HumanCode $orderNumber, ProjectReportsStatus $status): self;

    public function of(Identified $identifier): self;

    public function getIdentifier(): Identified;
}
