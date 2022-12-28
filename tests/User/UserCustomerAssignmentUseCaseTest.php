<?php

namespace Tests\User;

use Application\EventHandlers\User\AssignCustomerEventHandler;
use Application\UseCases\User\AssignCustomer\AssignCustomerInput;
use Application\UseCases\User\AssignCustomer\AssignCustomerUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\User\Events\CustomerAssignedToUser;
use Domain\Model\User\UnableToHandleUser;
use Domain\Model\User\UserEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class UserCustomerAssignmentUseCaseTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $userRepository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $this->userRepository = Mockery::spy(new UserRepositoryFake);
        $this->userRepository->shouldReceive('update');

        $assignCustomerEventHandler = new AssignCustomerEventHandler($this->userRepository);
        $this->domainEventBus = $this->faker->eventBus($assignCustomerEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_customer()
    {
        $orderId = Uuid::uuid4();
        $user = Mockery::spy(new UserEntity($this->domainEventBus));
        $user->withOrderId(Guid::from(Uuid::uuid4()), 1, 'John v', new Mobile($this->faker->phoneNumber), new Email($this->faker->email), new Password(123445, SmsVerificationCode::LENGTH), Guid::from(Uuid::uuid4()));

        $this->userRepository->shouldReceive('getByCustomer')
            ->andReturn($user);
        $assignProjectUseCase = new AssignCustomerUseCase($user, $this->userRepository);
        $this->given(new AssignCustomerInput($orderId, 0), $assignProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleUser());
    }

    public function test_executing_a_customer_assignment_usecase_successfully()
    {

        $orderId = Uuid::uuid4();
        $user = new UserEntity($this->domainEventBus);
        $user->withOrderId(Guid::from(Uuid::uuid4()), 1, 'John v', new Mobile($this->faker->phoneNumber), new Email($this->faker->email), new Password(123445, SmsVerificationCode::LENGTH), Guid::from(Uuid::uuid4()));

        $this->userRepository->shouldReceive('getByCustomer')
            ->andReturn($user);
        $assignProjectUseCase = new AssignCustomerUseCase($user, $this->userRepository);
        $this->given(new AssignCustomerInput($orderId, 198262), $assignProjectUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(CustomerAssignedToUser::class);
    }
}
