<?php

namespace Tests\Services;

use Common\Application\Event\Bus\DomainEventBus;
use Common\Specification\Specification;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderEntityNotFound;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\Specifications\PropertyAddressPreviouslyAssociated;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\Specifications\FollowUpInProgress;
use Domain\Services\PropertyAddressInUse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\TestCase;

class PropertyAddressInUseTest extends TestCase
{

    use WithFaker;

    protected Order $order;

    public function setUp(): void
    {
        parent::setUp();
        $this->order = new OrderEntity(new DomainEventBus);
        $this->order->fromExisting(Guid::from(Uuid::uuid4()), 12333, 1233, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile($this->faker->phonenumber));
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_when_mismatch_address_and_number()
    {
        $repositoryNoResult = Mockery::spy($this->faker->orderRepository());
        $repositoryNoResult->shouldReceive('getByAddressAndNumber')
            ->andReturn(new OrderEntityNotFound());

        $propertyAddressPreviouslyAssociatedSpecification = new PropertyAddressPreviouslyAssociated($repositoryNoResult, $this->order);
        $this->assertFalse($propertyAddressPreviouslyAssociatedSpecification->isSatisfiedBy(['address' => $this->faker->address, 'unit_number' => $this->faker->unique()->randomDigit()]));

        return $propertyAddressPreviouslyAssociatedSpecification;
    }

    public function test_should_match_address_and_number_successfully()
    {
        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('getByAddressAndNumber')
            ->andReturn($this->order);

        $propertyAddressPreviouslyAssociatedSpecification = new PropertyAddressPreviouslyAssociated($repository, $this->order);
        $this->assertTrue($propertyAddressPreviouslyAssociatedSpecification->isSatisfiedBy(['address' => $this->faker->address, 'unit_number' => $this->faker->unique()->randomDigit()]));

        return $propertyAddressPreviouslyAssociatedSpecification;
    }


    public function test_should_fail_when_mismatch_invalid_followup_project_status()
    {
        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => [
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_FINISHED]],
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_CANCELLED]],
            ]]);

        $followUpInProgressSpecification = new FollowUpInProgress($repository);
        $this->assertFalse($followUpInProgressSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => [
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_FINISHED]],
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::PROJECT_CANCELLED]],
            ]]);

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => []]);
        $followUpInProgressSpecification = new FollowUpInProgress($repository);
        $this->assertFalse($followUpInProgressSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));


        return $followUpInProgressSpecification;
    }

    public function test_should_consider_inprogress_project_status()
    {

        $projectStatusInProgressFiltered = array_values(Arr::except(ProjectReportsStatus::STATUS, [ProjectReportsStatus::PROJECT_FINISHED, ProjectReportsStatus::PROJECT_CANCELLED]));

        $projectStatusInProgress = array_map(function ($value) {
            return ['status' => $value];
        }, $projectStatusInProgressFiltered);

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getAll')
            ->andReturn(['data' => $projectStatusInProgress]);

        $followUpInProgressSpecification = new FollowUpInProgress($repository);
        $this->assertTrue($followUpInProgressSpecification->isSatisfiedBy(['order_id' => Uuid::uuid4()]));

        return $followUpInProgressSpecification;
    }

    /**
     * @depends test_should_fail_when_mismatch_address_and_number
     * @depends test_should_fail_when_mismatch_invalid_followup_project_status
     * @depends test_should_match_address_and_number_successfully
     * @depends test_should_consider_inprogress_project_status
     */
    public function test_should_fail_to_address_in_use(
        Specification $propertyAddressPreviouslyAssociatedSpecificationFailure,
        Specification $followUpInProgressSpecificationFailure,
        Specification $propertyAddressPreviouslyAssociatedSpecification,
        Specification $followUpInProgressSpecification
    ): void {
        $propertyAddressInUse = new PropertyAddressInUse($propertyAddressPreviouslyAssociatedSpecificationFailure, $followUpInProgressSpecificationFailure);
        $this->assertFalse($propertyAddressInUse->match($this->faker->address, $this->faker->unique()->randomDigit()));

        $propertyAddressInUse = new PropertyAddressInUse($propertyAddressPreviouslyAssociatedSpecificationFailure, $followUpInProgressSpecification);
        $this->assertFalse($propertyAddressInUse->match($this->faker->address, $this->faker->unique()->randomDigit()));

        $propertyAddressInUse = new PropertyAddressInUse($propertyAddressPreviouslyAssociatedSpecification, $followUpInProgressSpecificationFailure);
        $this->assertFalse($propertyAddressInUse->match($this->faker->address, $this->faker->unique()->randomDigit()));
    }

    /**
     * @depends test_should_match_address_and_number_successfully
     * @depends test_should_consider_inprogress_project_status
     */
    public function test_should_validate_successfully(Specification $propertyAddressPreviouslyAssociatedSpecification, Specification $followUpInProgressSpecification): void
    {
        $propertyAddressInUse = new PropertyAddressInUse($propertyAddressPreviouslyAssociatedSpecification, $followUpInProgressSpecification);
        $this->assertTrue($propertyAddressInUse->match($this->faker->address, $this->faker->unique()->randomDigit()));
    }
}
