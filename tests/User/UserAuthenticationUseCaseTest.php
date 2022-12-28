<?php

namespace Tests\User;

use Application\EventHandlers\User\UserAuthenticatedEventHandler;
use Application\UseCases\User\Authenticate\AuthenticateUserInput;
use Application\UseCases\User\Authenticate\AuthenticateUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\SmsVerificationCode;
use DG\BypassFinals;
use Domain\Model\User\Events\UserAuthenticated;
use Domain\Model\User\UnableToHandleUser;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class UserAuthenticationUseCaseTest extends AggregateRootTestCase
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
        $this->domainEventBus = $this->faker->eventBus($userAuthenticatedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_when_mismatched_sms_verification_code()
    {
        $user = new UserEntity($this->domainEventBus);
        $userModel = Mockery::spy(new UserModel());
        $personalAccessToken = new \Laravel\Sanctum\PersonalAccessToken();
        $userModel->shouldReceive('createToken')->andReturn(new \Laravel\Sanctum\NewAccessToken($personalAccessToken, 'slskslk22'));
        $userModel->shouldReceive('tokens->delete');
        $userModel->id = Uuid::uuid4();
        $userModel->mobile = $this->faker->phoneNumber;
        $userModel->email = $this->faker->email;
        $userModel->name = $this->faker->name;
        $this->userRepository->shouldReceive('get')
            ->andReturn($userModel);
        $authenticateUserUseCase = new AuthenticateUserUseCase($user, $this->userRepository);
        $this->given(new AuthenticateUserInput($this->faker->phoneNumber, 123456, Uuid::uuid4(), 'ASD654'), $authenticateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleUser());
    }

    public function test_executing_an_authentication_usecase_successfully()
    {

        $user = new UserEntity($this->domainEventBus);
        $smsVerificationCode = 123456;
        $userModel = $this->faker->userModel($smsVerificationCode);

        $this->userRepository->shouldReceive('get')
            ->andReturn($userModel);
        $authenticateUserUseCase = new AuthenticateUserUseCase($user, $this->userRepository);
        $this->given(new AuthenticateUserInput($userModel->mobile, $smsVerificationCode, $userModel->id, 'ASD654'), $authenticateUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(UserAuthenticated::class);
    }
}
