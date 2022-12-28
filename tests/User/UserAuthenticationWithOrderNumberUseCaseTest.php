<?php

namespace Tests\User;

use Application\EventHandlers\User\UserWithOrderNumberAuthenticatedEventHandler;
use Application\UseCases\User\AuthenticateWithOrderNumber\AuthenticateWithOrderNumberUserInput;
use Application\UseCases\User\AuthenticateWithOrderNumber\AuthenticateWithOrderNumberUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use DG\BypassFinals;
use Domain\Model\User\UnableToHandleUser;
use Domain\Model\User\User;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepository;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\OrderModel;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class UserAuthenticationWithOrderNumberUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $userRepository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $this->userRepository = Mockery::spy(new UserRepositoryFake);

        $userAuthenticatedEventHandler =  Mockery::mock(new UserWithOrderNumberAuthenticatedEventHandler());
        $userAuthenticatedEventHandler->shouldReceive('handle');
        $this->domainEventBus = $this->faker->eventBus($userAuthenticatedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_when_mismatched_order_number()
    {
        $user = new UserEntity($this->domainEventBus);
        $userModel = Mockery::spy(UserModel::class)->makePartial();
        $orderModel = Mockery::spy(OrderModel::class)->makePartial();

        $personalAccessToken = new \Laravel\Sanctum\PersonalAccessToken();
        $userModel->shouldReceive('createToken')->andReturn(new \Laravel\Sanctum\NewAccessToken($personalAccessToken, 'slskslk22'));
        $userModel->shouldReceive('tokens->delete');

        $userModel->id = Uuid::uuid4();
        $userModel->mobile = $this->faker->phoneNumber;
        $userModel->email = $this->faker->email;
        $userModel->name = $this->faker->name;
        $userModel->order = $orderModel;
        $userRepository = Mockery::spy(new UserRepository($userModel, $userModel));
        $userRepository->shouldReceive('getWithOrderAndMobile')
            ->andReturn($userModel);
        $authenticateUserUseCase = new AuthenticateWithOrderNumberUserUseCase($user, $userRepository);
        $this->given(new AuthenticateWithOrderNumberUserInput($this->faker->phoneNumber, 'ASD123'), $authenticateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleUser());
    }

    /*public function test_executing_an_authentication_with_order_number_usecase_successfully()
    {

        $user = new UserEntity($this->domainEventBus);
        $userModel = Mockery::mock(UserModel::class)->makePartial();
        $orderModel = Mockery::spy(OrderModel::class)->makePartial();
        $orderNumber = 'ASD876';
        $orderModel->order_number = $orderNumber;

        $userModel->id = Uuid::uuid4();
        $userModel->mobile = $this->faker->phoneNumber;
        $userModel->email = $this->faker->email;
        $userModel->name = $this->faker->name;
        $userModel->order = $orderModel;

        $this->userRepository->shouldReceive('getWithOrderAndMobile')
            ->andReturn($userModel);
        $authenticateUserUseCase = new AuthenticateWithOrderNumberUserUseCase($user, $this->userRepository);
        $this->given(new AuthenticateWithOrderNumberUserInput($userModel->mobile, $orderNumber), $authenticateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(UserWithOrderNumberAuthenticated::class);
    }*/
}
