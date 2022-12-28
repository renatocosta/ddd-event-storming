<?php

namespace Tests\User;

use Application\UseCases\User\PaymentMethod\PaymentMethodInput;
use Application\UseCases\User\PaymentMethod\PaymentMethodUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\User\UserEntity;
use Domain\Model\User\UserEntityNotFound;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Factory;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Proxy\ProxyBnB;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class UserUseCaseTest extends AggregateRootTestCase
{

    use WithFaker;

    private DomainEventBus $domainEventBus;

    public function setUp(): void
    {
        parent::setUp();
        $this->domainEventBus = new DomainEventBus;
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_identify_user_on_mismatched_sms_verification_code()
    {

        $this->expectException(UnableToHandleBusinessRules::class);

        $user = Mockery::spy(new UserEntity($this->app[DomainEventBus::class]));
        $user->withOrderId(Guid::from(Uuid::uuid4()), 'John v', new Mobile($this->faker->phoneNumber), new Email($this->faker->email), new Password(12344, SmsVerificationCode::LENGTH), Guid::from(Uuid::uuid4()));
        $user->identify();
    }

    public function test_should_fail_to_update_payment_method_when_mismatched_customer()
    {
        $user = Mockery::spy(new UserEntity($this->domainEventBus));
        $user->withCustomerId(Guid::from(Uuid::uuid4()), 'Robert K', new Mobile($this->faker->phoneNumber), new Email($this->faker->email), '', 4276111);

        $userRepository = Mockery::spy(new UserRepositoryFake());
        $userRepository->shouldReceive('getByCustomerAndUserId')
            ->andReturn(new UserEntityNotFound);

        $proxy = Mockery::spy(new ProxyBnB($this->app[Factory::class]));
        $proxy->shouldReceive('call');
        $proxy->shouldReceive('response');

        $paymentMethodUseCase = new PaymentMethodUseCase($user, $userRepository, $proxy);
        $this->given(new PaymentMethodInput(1255424, 'Review xyz', Uuid::uuid4()), $paymentMethodUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new ModelNotFoundException());
        $this->assertScenario();
    }
}
