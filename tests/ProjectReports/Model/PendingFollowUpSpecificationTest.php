<?php

namespace Tests\ProjectReports\Model;

use DG\BypassFinals;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\Specifications\PendingFollowUp;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\TestCase;

class PendingFollowUpSpecificationTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_when_pending_followup()
    {

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => []]);

        $pendingFollowUpSpecification = new PendingFollowUp($repository);
        $this->assertTrue($pendingFollowUpSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => [
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::ORDER_ACCEPTED]]
            ]]);

        $pendingFollowUpSpecification = new PendingFollowUp($repository);
        $this->assertFalse($pendingFollowUpSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => [
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::ORDER_ACCEPTED]],
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED]]
            ]]);

        $pendingFollowUpSpecification = new PendingFollowUp($repository);
        $this->assertFalse($pendingFollowUpSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));
    }
}
