<?php

namespace Domain\Services;

use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

final class ProjectReportsFollowUpCollection implements ProjectReportsMaterializable
{

    /**
     *
     * @var array<int, ProjectReports>
     */
    private array $followUp = [];

    public function __construct(private IProjectReportsRepository $projectRepository)
    {
    }

    public function filterFrom(HumanCode $orderNumber, ProjectReportsStatus ...$statuses): self
    {

        foreach ($statuses as $status) {
            try {
                $this->followUp[] = $this->projectRepository->getBy($orderNumber, $status);
            } catch (Throwable $e) {
            }
        }

        if (count($this->followUp) == 0) throw new ModelNotFoundException();

        return $this;
    }

    public function reconstituteFromEvent(): array
    {
        return $this->followUp;
    }
}
