<?php

namespace Tests\Order;

use Application\UseCases\Order\Confirm\ConfirmOrderInput;
use Application\UseCases\Order\Confirm\ConfirmOrderUseCase;
use Application\UseCases\Order\Create\CreateOrderInput;
use Application\UseCases\Order\Create\CreateOrderUseCase;
use Application\UseCases\Order\SendRating\SendRatingInput;
use Application\UseCases\Order\SendRating\SendRatingUseCase;
use Application\UseCases\Order\SendTip\SendTipInput;
use Application\UseCases\Order\SendTip\SendTipUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\Events\OrderCreated;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderEntityNotFound;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Factory;
use Infrastructure\Repositories\TransactionManagerFake;
use Infrastructure\Proxy\ProxyBnB;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;
use Ddd\BnbSchemaRegistry\UnableToHandleBnbSchemaRegistryException;

class OrderUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private int $smsVerificationCode;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $confirmEventHandler = $this->faker->confirmEventHandler($this->app, $this->faker->outBoxIntegrationRepository());
        $createEventHandler = $this->faker->createEventHandler($this->app);
        $this->domainEventBus = $this->faker->eventBus($createEventHandler, $confirmEventHandler);
        $this->smsVerificationCode = (new SmsVerificationCode())->code();
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_create_order_use_case_for_invalid_transition()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 124344, 123, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);

        $confirmOrderUseCase = new ConfirmOrderUseCase($order, $repository,  $this->app[TransactionManagerFake::class]);
        $this->given(new ConfirmOrderInput($this->faker->uuid, $this->smsVerificationCode), $confirmOrderUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleOrders());
        $this->assertScenario();
    }

    public function test_should_fail_to_confirm_order_use_case_for_invalid_transition()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 1244, 12343, new OrderStatus(OrderStatus::UNCREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);
        $confirmOrderUseCase = new ConfirmOrderUseCase($order, $repository,  $this->app[TransactionManagerFake::class]);
        $this->given(new ConfirmOrderInput($this->faker->uuid, $this->smsVerificationCode), $confirmOrderUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleOrders());
        $this->assertScenario();
    }

    public function test_executing_a_create_order_usecase_successfully(): void
    {

        $order = Mockery::spy(new OrderEntity($this->domainEventBus));

        $createOrderUseCase = new CreateOrderUseCase($order, $this->app[TransactionManagerFake::class]);
        $payload = $this->faker->payload()->asArray();
        $payload['event_type'] = 'OrderCreated';
        $this->given(new CreateOrderInput($payload, 1234, 12334), $createOrderUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(OrderCreated::class);

        $this->assertScenario();
        $order->shouldHaveReceived()->create()->once();
    }

    public function test_should_fail_to_confirm_order_usecase_when_reading_a_violated_payload(): void
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 1233, 123333, new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->invalidPayload(), new Mobile('5511987654214'))
            ->withSmsVerificationCode(new SmsVerificationCode($this->smsVerificationCode));
        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);

        $confirmOrderUseCase = new ConfirmOrderUseCase($order, $repository, $this->app[TransactionManagerFake::class]);

        $this->given(new ConfirmOrderInput(Uuid::uuid4(), $this->smsVerificationCode), $confirmOrderUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleBnbSchemaRegistryException());
    }

    public function test_executing_a_confirm_order_usecase_successfully(): void
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12233, 125343, new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'))
            ->withSmsVerificationCode(new SmsVerificationCode($this->smsVerificationCode));
        $repository = Mockery::spy($this->faker->orderRepository());
        $repository->shouldReceive('get')
            ->andReturn($order);

        $confirmOrderUseCase = new ConfirmOrderUseCase($order, $repository, $this->app[TransactionManagerFake::class]);

        $this->given(new ConfirmOrderInput(Uuid::uuid4(), $this->smsVerificationCode), $confirmOrderUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(OrderConfirmed::class);

        $this->assertScenario();
        $order->shouldHaveReceived()->confirm()->once();
    }

    public function test_should_fail_to_send_review_when_mismatched_project()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12334, 12345, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $orderRepository->shouldReceive('getByProjectAndCustomerId')
            ->andReturn(new OrderEntityNotFound);
        $proxy = Mockery::spy(new ProxyBnB($this->app[Factory::class]));
        $proxy->shouldReceive('call');
        $proxy->shouldReceive('response');

        $sendRatingUseCase = new SendRatingUseCase($order, $orderRepository, $proxy);
        $this->given(new SendRatingInput(1255424, 3, 'Review xyz', 1233), $sendRatingUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new ModelNotFoundException());
        $this->assertScenario();
    }

    public function test_should_fail_to_send_tip_when_mismatched_project()
    {
        $order = Mockery::spy(new OrderEntity($this->domainEventBus));
        $order->fromExisting(Guid::from(Uuid::uuid4()), 1233, 12433, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('5511987654214'));

        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $orderRepository->shouldReceive('getByProjectAndCustomerId')
            ->andReturn(new OrderEntityNotFound);

        $proxy = Mockery::spy(new ProxyBnB($this->app[Factory::class]));
        $proxy->shouldReceive('call');
        $proxy->shouldReceive('response');

        $sendRatingUseCase = new SendTipUseCase($order, $orderRepository, $proxy);
        $this->given(new SendTipInput(87822, 10.32, 'USD', 13233), $sendRatingUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new ModelNotFoundException());
        $this->assertScenario();
    }
}
