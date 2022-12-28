<?php

namespace Domain\Services;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\ProjectReportsStatus;

interface ProjectReportsMaterializable
{
    public function filterFrom(HumanCode $orderNumber, ProjectReportsStatus ...$statuses): self;

    public function reconstituteFromEvent(): array;
}
