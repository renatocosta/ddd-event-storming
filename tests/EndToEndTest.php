<?php

namespace Tests;

use Application\EventHandlers\User\AssignCustomerEventHandler;
use Application\Orchestration\ChangeProjectReportsOrchestrator;
use Application\Orchestration\ConfirmOrderOrchestrator;
use Application\Orchestration\CreateOrderOrchestrator;
use Application\UseCases\Order\Confirm\ConfirmOrderInput;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsInput;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\Events\CustomerCreated;
use Domain\Model\Customer\Events\CustomerOrderLocationAdded;
use Domain\Model\Customer\Events\CustomerOrderPaymentAdded;
use Domain\Model\Customer\ICustomerRepository;
use Domain\Model\Notification\Events\NotificationEmailPublished;
use Domain\Model\Notification\Events\NotificationSmsPublished;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\Events\OrderCreated;
use Domain\Model\Order\Events\ProjectAssignedToOrder;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderStatus;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusChanged;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\MessageReport\MessageRecipient;
use Domain\Model\ProjectReports\MessageReport\MessageRecipientable;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\Property\Events\PropertyCreated;
use Domain\Model\Property\IPropertyRepository;
use Domain\Model\Property\Property;
use Domain\Model\User\Events\CustomerAndPaymentEnrolled;
use Domain\Model\User\Events\CustomerAssignedToUser;
use Domain\Model\User\Events\UserAuthenticated;
use Domain\Model\User\Events\UserIdentified;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\TransactionManagerFake;
use Infrastructure\Repositories\UserRepository;
use Infrastructure\Kafka\ProducerFake;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;
use Ddd\BnbEnqueueClient\ConnectionManager;
use Illuminate\Database\DatabaseManager;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Infrastructure\Framework\Entities\OrderModel;
use Mockery\MockInterface;
use Stripe\BaseStripeClient;
use YlsIdeas\FeatureFlags\Facades\Features;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Illuminate\Contracts\Cache\Repository as Cache;
use Infrastructure\Repositories\CustomerRepository;
use Infrastructure\Repositories\PropertyRepository;
use stdClass;

class EndToEndTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    private $slackEventHandler;

    private int $smsVerificationCode;

    private User $user;

    private Order $order;

    private Customer $customer;

    private Property $property;

    private IOrderRepository $orderRepository;

    private IUserRepository $userRepository;

    private ICustomerRepository $customerRepository;

    private IPropertyRepository $propertyRepository;

    private Producer $kafkaProducer;

    private $featureFlag;

    private $orderModel;

    use WithFaker;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();

        $this->domainEventBus = new DomainEventBus;
        $this->smsVerificationCode = (new SmsVerificationCode())->code();

        $this->orderModel = Mockery::spy(new OrderModel());
        $this->orderModel->shouldReceive('on');

        $userModel = $this->faker->userModel($this->smsVerificationCode);

        $this->user = Mockery::spy($this->app->makeWith(User::class, [DomainEventBus::class => $this->domainEventBus]));
        $this->user->of(Guid::from(Uuid::uuid4()), 'John v', new Mobile($this->faker->phoneNumber), new Email($this->faker->email));
        $this->user->shouldReceive('token')
            ->andReturn('18|W3lHgv3zOnrjCNNil13Cf5M8kfND9ImN6jOkQHaB');
        $this->user->shouldReceive('andFinally');
        $this->user->shouldReceive('setIdentifier')->with(Guid::from(Uuid::uuid4()));

        $this->userRepository = Mockery::spy(new UserRepository($userModel, $userModel));
        $this->userRepository->shouldReceive('get')
            ->andReturn($userModel);

        $this->userRepository->shouldReceive('getWithOrder')
            ->andReturn($userModel);

        $this->userRepository->shouldReceive('getByCustomer')
            ->andReturn($this->user);

        $this->userRepository->shouldReceive('update');
        $this->userRepository->shouldReceive('create')->andReturn($userModel);

        $this->order = new OrderEntity($this->domainEventBus);
        $this->orderRepository = Mockery::spy($this->faker->orderRepository());

        $customerModel = $this->faker->customerModel();
        $this->customerRepository = new CustomerRepository($customerModel, $customerModel, $this->faker->customerOrderPaymentModel(), $this->faker->customerOrderLocationModel());

        $propertyModel = $this->faker->propertyModel();
        $this->propertyRepository = new PropertyRepository($propertyModel, $propertyModel);

        $this->kafkaProducer = new ProducerFake(new ConnectionManager([]));
        $this->featureFlag = Mockery::mock('alias:Manager');
        $this->featureFlag->shouldReceive('accessible')->andReturn(true);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_executing_a_create_order_end_to_end_successfully(): void
    {

        $payload = $this->faker->payload()->asArray();
        $payload['event_type'] = 'OrderCreated';

        $createOrderOrchestrator = $this->app->makeWith(CreateOrderOrchestrator::class, [DomainEventBus::class => $this->domainEventBus, Customer::class => Customer::class, ICustomerRepository::class => $this->customerRepository, Property::class => Property::class, IPropertyRepository::class => $this->propertyRepository, Order::class => $this->order, User::class => $this->user, IOrderRepository::class => $this->orderRepository, IUserRepository::class => $this->userRepository,  DatabaseManager::class => $this->app[TransactionManagerFake::class], Features::class => $this->featureFlag, Producer::class => $this->kafkaProducer, BaseStripeClient::class => $this->faker->stripe(), OutboxIntegrationEventsRepository::class => $this->faker->outBoxIntegrationRepository()]);

        $this->given($payload, $createOrderOrchestrator)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(
                CustomerCreated::class,
                PropertyCreated::class,
                OrderCreated::class,
                CustomerOrderLocationAdded::class,
                UserIdentified::class,
                CustomerAndPaymentEnrolled::class,
                CustomerOrderPaymentAdded::class,
                NotificationSmsPublished::class
            );

        $this->assertScenario();
    }

    public function test_executing_a_confirm_order_end_to_end_successfully(): void
    {
        $payload = $this->faker->payload()->asArray();
        $payload['event_type'] = 'OrderConfirmed';
        $this->order->fromExisting(Guid::from(Uuid::uuid4()), 11222, 123, new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511900000000'));
        $this->orderRepository->shouldReceive('get')
            ->andReturn($this->order);

        $confirmedOrderOrchestrator = $this->app->makeWith(ConfirmOrderOrchestrator::class, [DomainEventBus::class => $this->domainEventBus, Order::class => $this->order, User::class => $this->user, IOrderRepository::class => $this->orderRepository, IUserRepository::class => $this->userRepository,  DatabaseManager::class => $this->app[TransactionManagerFake::class], Features::class => $this->featureFlag, Producer::class => $this->kafkaProducer, OutboxIntegrationEventsRepository::class => $this->faker->outBoxIntegrationRepository()]);

        $this->given(new ConfirmOrderInput(Guid::from(Uuid::uuid4()), $this->smsVerificationCode), $confirmedOrderOrchestrator)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(
                OrderConfirmed::class,
                UserAuthenticated::class,
                NotificationEmailPublished::class,
                NotificationSmsPublished::class
            );

        $this->assertScenario();
    }

    public function test_executing_a_change_project_usecase_successfully(): void
    {

        $projectReports = Mockery::spy(new ProjectReportsEntity($this->domainEventBus));
        $projectReports->from(Guid::from(Uuid::uuid4()), new HumanCode(), new ProjectReportsStatus(ProjectReportsStatus::ORDER_ACCEPTED), $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order'), new Mobile('551100000000'));

        $projectReportsRepository = Mockery::spy($this->faker->projectReportsRepository());
        $projectReportsRepository->shouldReceive('getStateByOrderNumber')
            ->andReturn($projectReports);

        $orderRepo = Mockery::spy($this->faker->orderRepository());
        $order = new OrderEntity($this->domainEventBus);
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12334, 12356, new OrderStatus(OrderStatus::CREATED), new HumanCode(), $this->faker->payload(), new Mobile('5511900000000'));

        $orderRepo->shouldReceive('getById')->with(Guid::from(Uuid::uuid4()))->andReturn($order);
        $orderRepo->shouldReceive('get')->andReturn($order);

        $cache = $this->mock(Cache::class, function (MockInterface $mock) use ($projectReports) {
            $mock->shouldReceive('forget');
            $mock->shouldReceive('remember')->andReturn($projectReports);
        });

        $orderRepo = Mockery::spy($this->faker->orderRepository());
        $orderRepo->shouldReceive('getById')->andReturn($order);
        $orderRepo->shouldReceive('get')->andReturn($order);

        $assignCustomerEventHandler = new AssignCustomerEventHandler($this->userRepository);
        $this->domainEventBus->subscribe($assignCustomerEventHandler);

        $user = Mockery::spy(new UserEntity($this->domainEventBus));
        $user->withOrderId(Guid::from(Uuid::uuid4()), 122, 'John v', new Mobile($this->faker->phoneNumber), new Email($this->faker->email), new Password(123445, SmsVerificationCode::LENGTH), Guid::from(Uuid::uuid4()));

        $messageRecipient = new MessageRecipient(new stdClass);

        $changeProjectOrchestrator = $this->app->makeWith(ChangeProjectReportsOrchestrator::class, [DomainEventBus::class => $this->domainEventBus, ProjectReports::class => $projectReports, IProjectReportsRepository::class => $projectReportsRepository,  DatabaseManager::class => $this->app[TransactionManagerFake::class], Features::class => $this->featureFlag, Producer::class => $this->kafkaProducer, OutboxIntegrationEventsRepository::class => $this->faker->outBoxIntegrationRepository(), IUserRepository::class => $this->userRepository, IOrderRepository::class => $orderRepo, Cache::class => $cache, Order::class => $order, User::class => $user, MessageRecipientable::class => $messageRecipient]);
        $payload = $this->faker->payloadProjectReports(ProjectReportsStatus::ORDER_ACCEPTED, 'Order');

        $this->given(new ChangeProjectReportsInput(Guid::from(Uuid::uuid4()), $payload), $changeProjectOrchestrator)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(
                ProjectReportsStatusChanged::class,
                CustomerAssignedToUser::class,
                ProjectAssignedToOrder::class,
                NotificationEmailPublished::class,
                NotificationSmsPublished::class
            );

        $this->assertScenario();
    }
}
