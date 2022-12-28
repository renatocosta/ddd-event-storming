<?php

namespace Tests\ProjectReports\Model;

use DG\BypassFinals;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Utilities\TestCase;

class ProjectReportsStatusTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_status_when_values_are_missing(): void
    {
        $this->expectException(UnableToHandleProjectReports::class);
        new ProjectReportsStatus($this->faker->invalidProjectReportsStatus());
    }

    public function test_entering_a_list_of_status_successfully(): void
    {
        foreach (ProjectReportsStatus::STATUS as $status => $statusId)
            $projectReportsStatus = new ProjectReportsStatus($status);
        $this->assertEquals($projectReportsStatus, $status);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
