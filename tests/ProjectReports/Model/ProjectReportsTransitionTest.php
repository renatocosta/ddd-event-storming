<?php

namespace Tests\ProjectReports\Model;

use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use DG\BypassFinals;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\ProjectReportsEntityNotFound;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\State\ProjectReportsTransition;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\TestCase;

class ProjectReportsTransitionTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_state_for_existent_project(): void
    {
        $this->expectException(UnableToHandleProjectReports::class);
        $project = $this->faker->projectReports();
        $project->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED), new Payload('{}'), new Mobile('112344411'));
        new ProjectReportsTransition($project, new ProjectReportsStatus(ProjectReportsStatus::PROJECT_FINISHED));
    }

    public function test_entering_a_valid_status_for_existent_project(): void
    {
        $projectReports = new ProjectReportsEntity(new DomainEventBus);
        $orderIdentifier = Guid::from(Uuid::uuid4());
        $projectReports->from($orderIdentifier, new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::PROJECT_FINISHED), new Payload('{}'), new Mobile('124444'));
        $projectReportsTransition = new ProjectReportsTransition($projectReports, new ProjectReportsStatus(ProjectReportsStatus::PROJECT_PAYMENT_SUCCEEDED));
        $this->assertNotEmpty($projectReportsTransition);
    }

    public function test_should_fail_to_invalid_state_for_non_existent_project(): void
    {
        $this->expectException(UnableToHandleProjectReports::class);
        $projectReports = new ProjectReportsEntityNotFound();
        new ProjectReportsTransition($projectReports, new ProjectReportsStatus(ProjectReportsStatus::PROJECT_STARTED));
    }

    public function test_entering_a_valid_status_for_non_existent_project(): void
    {
        $projectReports = new ProjectReportsEntityNotFound();
        $projectReportsTransition = new ProjectReportsTransition($projectReports, new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED));
        $this->assertNotEmpty($projectReportsTransition);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
