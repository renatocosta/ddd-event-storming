<?php

namespace Tests\User;

use Application\EventHandlers\User\UserUpdatedEventHandler;
use Application\UseCases\User\Update\UpdateUserInput;
use Application\UseCases\User\Update\UpdateUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use DG\BypassFinals;
use Domain\Model\User\Events\UserUpdated;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\OrderModel;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class UpdateUserUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $userRepository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $this->userRepository = Mockery::spy(new UserRepositoryFake);

        $userUpdatedEventHandler = Mockery::mock(new UserUpdatedEventHandler($this->userRepository));
        $userUpdatedEventHandler->shouldReceive('handle');
        $this->domainEventBus = $this->faker->eventBus($userUpdatedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_executing_update_user_usecase_successfully()
    {

        $user = new UserEntity($this->domainEventBus);
        $userModel = Mockery::spy(new UserModel());
        $personalAccessToken = new \Laravel\Sanctum\PersonalAccessToken();
        $userModel->shouldReceive('createToken')->andReturn(new \Laravel\Sanctum\NewAccessToken($personalAccessToken, 'slskslk22'));
        $userModel->shouldReceive('tokens->delete');
        $userModel->id = Uuid::uuid4();
        $userModel->customer_id = 2344;
        $userModel->mobile = $this->faker->phoneNumber;
        $userModel->email = $this->faker->email;
        $userModel->name = $this->faker->name;
        $userModel->order = Mockery::spy(new OrderModel());
        $userModel->order->id = Uuid::uuid4();

        $this->userRepository->shouldReceive('getWithOrder')
            ->andReturn($userModel);
        $updateUserUseCase = new UpdateUserUseCase($user, $this->userRepository);
        $this->given(new UpdateUserInput(Uuid::uuid4(), $this->faker->phoneNumber), $updateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(UserUpdated::class);
    }
}
