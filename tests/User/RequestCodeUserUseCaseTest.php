<?php

namespace Tests\User;

use Application\EventHandlers\User\UserVerificationCodeRequestedEventHandler;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\User\RequestCode\RequestCodeUserInput;
use Application\UseCases\User\RequestCode\RequestCodeUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\SmsVerificationCode;
use DG\BypassFinals;
use Domain\Model\User\Events\UserVerificationCodeRequested;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepositoryFake;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class RequestCodeUserUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $userRepository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $this->userRepository = Mockery::spy(new UserRepositoryFake);

        $this->domainEventBus = new DomainEventBus;
        $userVerificationCodeRequestedEventHandler = Mockery::mock(new UserVerificationCodeRequestedEventHandler($this->faker->identifyUserUseCase($this->app, $this->domainEventBus), $this->app[INotificationSmsPublishUseCase::class]));
        $userVerificationCodeRequestedEventHandler->shouldReceive('handle');
        $this->domainEventBus->subscribe($userVerificationCodeRequestedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_executing_request_code_usecase_successfully()
    {

        $user = new UserEntity($this->domainEventBus);
        $userModel = $this->faker->userModel();

        $this->userRepository->shouldReceive('getWithOrder')
            ->andReturn($userModel);
        $requestCodeUserUseCase = new RequestCodeUserUseCase($user, $this->userRepository, $this->app[SmsVerificationCode::class]);
        $this->given(new RequestCodeUserInput(Uuid::uuid4()), $requestCodeUserUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(UserVerificationCodeRequested::class);
    }
}
