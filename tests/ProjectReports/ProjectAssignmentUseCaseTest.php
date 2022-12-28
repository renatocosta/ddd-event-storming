<?php

namespace Tests\ProjectReports;

use Application\EventHandlers\Order\AssignProjectEventHandler;
use Application\UseCases\Order\AssignProject\AssignProjectInput;
use Application\UseCases\Order\AssignProject\AssignProjectUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\Events\ProjectAssignedToOrder;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Domain\Services\OrderTrackable;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class ProjectAssignmentUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private OrderTrackable $orderTracker;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $orderRepository->shouldReceive('update');
        $assignProjectEventHandler = new AssignProjectEventHandler($orderRepository);
        $this->domainEventBus = $this->faker->eventBus($assignProjectEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_project()
    {

        $order = new OrderEntity($this->domainEventBus);
        $order->withProjectId(Guid::from(Uuid::uuid4()), new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'), 0);

        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $orderRepository->shouldReceive('get')
            ->andReturn($order);

        $assignProjectUseCase = new AssignProjectUseCase($order, $orderRepository);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new AssignProjectInput($orderId, 0), $assignProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleProjectReports());
    }

    public function test_executing_a_project_assignment_usecase_successfully()
    {
        $order = new OrderEntity($this->domainEventBus);
        $order->withProjectId(Guid::from(Uuid::uuid4()), new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'), 1222);

        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $orderRepository->shouldReceive('get')
            ->andReturn($order);

        $assignProjectUseCase = new AssignProjectUseCase($order, $orderRepository);
        $orderId = Guid::from(Uuid::uuid4());
        $this->given(new AssignProjectInput($orderId, 12344), $assignProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(ProjectAssignedToOrder::class);

        $this->assertScenario();
    }
}
