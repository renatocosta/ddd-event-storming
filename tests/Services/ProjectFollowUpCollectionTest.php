<?php

namespace Tests\Services;

use Common\ValueObjects\Misc\HumanCode;
use DG\BypassFinals;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Services\ProjectReportsFollowUpCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Utilities\TestCase;

class ProjectFollowUpCollectionTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_getting_successfully(): void
    {

        $statuses = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_STARTED, ProjectReportsStatus::PROJECT_FINISHED];
        $totalOfStatusRunned = count($statuses);

        $projectReportsRepository = Mockery::mock($this->faker->projectReportsRepository);
        $projectReportsRepository->shouldReceive('getBy')->times($totalOfStatusRunned);

        $projectReportsFollowUpCollection = new ProjectReportsFollowUpCollection($projectReportsRepository);

        $projectReportsFollowUpCollection->filterFrom(
            new HumanCode(),
            ...array_map(fn ($projectReportsStatus): ProjectReportsStatus => new ProjectReportsStatus($projectReportsStatus), $statuses)
        );

        $this->assertCount($totalOfStatusRunned, $projectReportsFollowUpCollection->reconstituteFromEvent());
    }

    public function test_should_fail_when_follow_up_no_results(): void
    {

        $this->expectException(ModelNotFoundException::class);
        $statuses = [ProjectReportsStatus::ORDER_ACCEPTED, ProjectReportsStatus::PROJECT_STARTED, ProjectReportsStatus::PROJECT_FINISHED];

        $projectReportsRepository = Mockery::mock($this->faker->projectReportsRepository);
        $projectReportsRepository->shouldReceive('getBy')->andReturnUsing(function () {
            throw new ModelNotFoundException();
        });

        $projectReportsFollowUpCollection = new ProjectReportsFollowUpCollection($projectReportsRepository);

        $projectReportsFollowUpCollection->filterFrom(
            new HumanCode(),
            ...array_map(fn ($projectReportsStatus): ProjectReportsStatus => new ProjectReportsStatus($projectReportsStatus), $statuses)
        )
            ->reconstituteFromEvent();
    }
}
