<?php

namespace Tests\ProjectReports;

use Application\UseCases\ProjectReports\Change\ChangeProjectReportsInput;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderStatus;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Domain\Services\OrderTrackable;
use Domain\Services\OrderTracker;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\TransactionManagerFake;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class ProjectUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private OrderTrackable $orderTracker;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();

        $this->domainEventBus = new DomainEventBus();
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 123343, 12334, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511954321234'));

        $orderRepo = Mockery::spy($this->faker->orderRepository());
        $this->orderTracker = Mockery::spy(new OrderTracker($orderRepo));
        $this->orderTracker->shouldReceive('fetchCurrent')->andReturn($order);

        $project = new ProjectReportsEntity($this->domainEventBus);
        $project->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::ORDER_ACCEPTED), $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order'), new Mobile('5511987652211'));

        $statusChangedEventHandler = $this->faker->changeProjectReportsEventHandler($this->app, $order, $project, $this->orderTracker);
        $this->domainEventBus->subscribe($statusChangedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_change_project_use_case_for_invalid_payload()
    {

        $project = Mockery::spy(new ProjectReportsEntity($this->domainEventBus));
        $changeProjectUseCase = new ChangeProjectReportsUseCase($project, $this->faker->projectReportsRepository(), $this->orderTracker, $this->app[TransactionManagerFake::class]);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new ChangeProjectReportsInput($orderId, $this->faker->invalidProjectReportsPayload()), $changeProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleBusinessRules());
        $this->assertScenario();
    }

    public function test_should_fail_to_change_project_use_case_for_mismatch_event_type()
    {

        $project = Mockery::spy(new ProjectReportsEntity($this->domainEventBus));
        $project->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::ORDER_ACCEPTED), $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order'), new Mobile('5511987652211'));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getStateByOrderNumber')
            ->andReturn($project);

        $validPayload = $this->faker->payloadProjectReports(ProjectReportsStatus::PROJECT_FINISHED, 'Order');

        $changeProjectUseCase = new ChangeProjectReportsUseCase($project, $repository, $this->orderTracker,  $this->app[TransactionManagerFake::class]);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new ChangeProjectReportsInput($orderId, $validPayload), $changeProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleProjectReports());
        $this->assertScenario();
    }

    public function test_executing_a_change_project_usecase_successfully(): void
    {

        $project = Mockery::spy(new ProjectReportsEntity($this->domainEventBus));
        $project->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::CONFIRMED), $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order'), new Mobile('5511987652211'));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getStateByOrderNumber')
            ->andReturn($project);

        $validPayload = $this->faker->payloadProjectReports(ProjectReportsStatus::PROJECT_CANCELLED, 'Order');
        $changeProjectUseCase = new ChangeProjectReportsUseCase($project, $repository, $this->orderTracker,  $this->app[TransactionManagerFake::class]);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new ChangeProjectReportsInput($orderId, $validPayload), $changeProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(ProjectReportsStatusChanged::class);
        $this->assertScenario();
    }

    public function test_executing_a_change_project_usecase_successfully_when_relationships_mapped(): void
    {

        $project = Mockery::spy(new ProjectReportsEntity($this->domainEventBus));
        $project->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED), $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order'), new Mobile('5511987652211'));

        $repository = Mockery::spy($this->faker->projectReportsRepository());
        $repository->shouldReceive('getStateByOrderNumber')
            ->andReturn($project);

        $validPayload = $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order');
        $changeProjectUseCase = new ChangeProjectReportsUseCase($project, $repository, $this->orderTracker,  $this->app[TransactionManagerFake::class]);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new ChangeProjectReportsInput($orderId, $validPayload), $changeProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(ProjectReportsStatusChanged::class);
        $this->assertScenario();
    }
}
