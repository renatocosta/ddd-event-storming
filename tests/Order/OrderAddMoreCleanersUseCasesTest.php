<?php

namespace Tests\Order;

use Application\UseCases\Order\AddMoreCleaners\AddMoreCleanersInput;
use Application\UseCases\Order\AddMoreCleaners\AddMoreCleanersUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\Events\OrderUpdated;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\Specifications\PendingFollowUp;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class OrderAddMoreCleanersUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $updateEventHandler = $this->faker->updateEventHandler($this->app);
        $this->domainEventBus = $this->faker->eventBus($updateEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_add_more_cleaners_use_case_when_invalid_transition()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12333, 123454, new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);


        $projectReportsRepository = Mockery::spy($this->faker->projectReportsRepository());
        $projectReportsRepository->shouldReceive('getAll')
            ->andReturn(['data' => []]);

        $pendingFollowUp = new PendingFollowUp($projectReportsRepository);

        $addMoreCleanersUseCase = new AddMoreCleanersUseCase($order, $repository,  $pendingFollowUp);
        $this->given(new AddMoreCleanersInput($this->faker->uuid, [['id' => 123, 'name' => 'clean xpto']]), $addMoreCleanersUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleOrders());
        $this->assertScenario();
    }

    public function test_should_fail_to_add_more_cleaners_use_case_when_followup_is_on()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 1234, 123455, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);


        $projectReportsRepository = Mockery::spy($this->faker->projectReportsRepository());
        $projectReportsRepository->shouldReceive('getAll')
            ->andReturn(['data' => [
                ['status' => ProjectReportsStatus::STATUS[ProjectReportsStatus::ORDER_ACCEPTED]]
            ]]);

        $pendingFollowUp = new PendingFollowUp($projectReportsRepository);

        $addMoreCleanersUseCase = new AddMoreCleanersUseCase($order, $repository,  $pendingFollowUp);
        $this->given(new AddMoreCleanersInput($this->faker->uuid, [['id' => 123, 'name' => 'clean xpto']]), $addMoreCleanersUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleOrders());
        $this->assertScenario();
    }

    public function test_add_more_cleaners_usecase_successfully(): void
    {

        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12333, 1234666, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);

        $projectReportsRepository = Mockery::spy($this->faker->projectReportsRepository());
        $projectReportsRepository->shouldReceive('getAll')
            ->andReturn(['data' => []]);

        $pendingFollowUp = new PendingFollowUp($projectReportsRepository);

        $addMoreCleanersUseCase = new AddMoreCleanersUseCase($order, $repository,  $pendingFollowUp);

        $this->given(new AddMoreCleanersInput($this->faker->uuid, [['id' => 123, 'name' => 'clean xpto']]), $addMoreCleanersUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(OrderUpdated::class);

        $this->assertScenario();
        $order->shouldHaveReceived()->update()->once();
    }
}
