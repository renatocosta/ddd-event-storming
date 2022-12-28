<?php

namespace Tests\User;

use Application\EventHandlers\User\EnrollCustomerAndPaymentEventHandler;
use Application\EventHandlers\User\UserAuthenticatedEventHandler;
use Application\EventHandlers\User\UserIdentifiedEventHandler;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\User\Identify\IdentifyUserInput;
use Application\UseCases\User\Identify\IdentifyUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\SmsVerificationCode;
use DG\BypassFinals;
use Domain\Model\Order\OrderEntity;
use Domain\Model\User\Events\CustomerAndPaymentEnrolled;
use Domain\Model\User\Events\UserIdentified;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class IdentifyUserUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $userRepository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $this->userRepository = Mockery::spy(new UserRepositoryFake);

        $userAuthenticatedEventHandler = new UserAuthenticatedEventHandler($this->userRepository, $this->app[SmsVerificationCode::class]);
        $userIdentifiedEventHandler =  Mockery::mock(new UserIdentifiedEventHandler($this->userRepository));
        $userIdentifiedEventHandler->shouldReceive('handle');
        $order = Mockery::spy(new OrderEntity($this->app[DomainEventBus::class]));
        $orderRepository = Mockery::spy($this->faker->orderRepository());
        $enrollCustomerAndPaymentEventHandler = Mockery::spy(new EnrollCustomerAndPaymentEventHandler($order, $orderRepository, $this->faker->stripe(), $this->app[INotificationSmsPublishUseCase::class]));
        $enrollCustomerAndPaymentEventHandler->shouldReceive('handle');

        $this->domainEventBus = $this->faker->eventBus($userAuthenticatedEventHandler, $userIdentifiedEventHandler, $enrollCustomerAndPaymentEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_executing_identification_usecase_successfully()
    {

        $user = new UserEntity($this->domainEventBus);
        $userModel = Mockery::spy(new UserModel());
        $personalAccessToken = new \Laravel\Sanctum\PersonalAccessToken();
        $userModel->shouldReceive('createToken')->andReturn(new \Laravel\Sanctum\NewAccessToken($personalAccessToken, 'slskslk22'));
        $userModel->shouldReceive('tokens->delete');

        $this->userRepository->shouldReceive('getWithOrderAndMobile')
            ->andReturn($userModel);
        $authenticateUserUseCase = new IdentifyUserUseCase($user, $this->app[SmsVerificationCode::class]);
        $this->given(new IdentifyUserInput($this->faker->name, $this->faker->email, $this->faker->phoneNumber, Uuid::uuid4(), 134), $authenticateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(UserIdentified::class, CustomerAndPaymentEnrolled::class);
    }
}
