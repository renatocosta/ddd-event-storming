<?php

namespace Tests\Customer;

use Application\EventHandlers\Customer\CustomerCreatedEventHandler;
use Application\EventHandlers\Customer\CustomerOrderLocationAddedEventHandler;
use Application\EventHandlers\Customer\CustomerOrderPaymentAddedEventHandler;
use Application\UseCases\Customer\Create\CreateCustomerInput;
use Application\UseCases\Customer\Create\CreateCustomerUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\AddOrderLocationInput;
use Application\UseCases\Customer\CustomerAddOrderLocation\AddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\AddOrderPaymentInput;
use Application\UseCases\Customer\CustomerAddOrderPayment\AddOrderPaymentUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;
use DG\BypassFinals;
use Domain\Model\Customer\CustomerEntity;
use Domain\Model\Customer\Events\CustomerCreated;
use Domain\Model\Customer\Events\CustomerOrderLocationAdded;
use Domain\Model\Customer\Events\CustomerOrderPaymentAdded;
use Domain\Model\Customer\UnableToHandleCustomers;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\CustomerRepositoryFake;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\AggregateRootTestCase;

class CustomerUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $repository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();

        $this->repository = new CustomerRepositoryFake();

        $createEventHandler = \Mockery::spy(new CustomerCreatedEventHandler($this->repository));
        $createEventHandler->shouldReceive('handle');
        $orderLocationAddedEventHandler = \Mockery::spy(new CustomerOrderLocationAddedEventHandler($this->repository));
        $orderLocationAddedEventHandler->shouldReceive('handle');
        $orderPaymentAddedEventHandler = \Mockery::spy(new CustomerOrderPaymentAddedEventHandler($this->repository));
        $orderPaymentAddedEventHandler->shouldReceive('handle');
        $this->domainEventBus = new DomainEventBus();
        $this->domainEventBus->subscribers($createEventHandler, $orderLocationAddedEventHandler, $orderPaymentAddedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_create_customer_use_case_for_invalid_input()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));

        $createCustomerUseCase = new CreateCustomerUseCase($customer);
        $this->given(new CreateCustomerInput($this->faker->realTextBetween(150, 300), $this->faker->phoneNumber(), $this->faker->email(), $this->faker->countryCode()), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleBusinessRules())
            ->assertScenario();
    }

    public function test_executing_a_create_customer_usecase_successfully()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));

        $createCustomerUseCase = new CreateCustomerUseCase($customer);
        $this->given(new CreateCustomerInput($this->faker->name(), $this->faker->phoneNumber(), $this->faker->email(), $this->faker->countryCode()), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(CustomerCreated::class)
            ->assertScenario();
    }

    public function test_should_fail_to_add_order_location_use_case_for_invalid_input()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));
        $customer->fromExisting(123, new Name($this->faker->name()), new Mobile($this->faker->phoneNumber()), new Email($this->faker->email()), new Country($this->faker->countryCode()));

        $createCustomerUseCase = new AddOrderLocationUseCase($customer);
        $this->given(new AddOrderLocationInput($this->faker->realTextBetween(60, 100), Guid::from(Uuid::uuid4()), 122), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleBusinessRules())
            ->assertScenario();
    }

    public function test_executing_add_customer_location_usecase_successfully()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));
        $customer->fromExisting(123, new Name($this->faker->name()), new Mobile($this->faker->phoneNumber()), new Email($this->faker->email()), new Country($this->faker->countryCode()));

        $createCustomerUseCase = new AddOrderLocationUseCase($customer);
        $this->given(new AddOrderLocationInput('LKHSLKKSHKLSHLKSKHSLHSL', Guid::from(Uuid::uuid4()), 122), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(CustomerOrderLocationAdded::class)
            ->assertScenario();
    }

    public function test_should_fail_to_add_order_payment_use_case_for_invalid_input()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));
        $customer->fromExisting(123, new Name($this->faker->name()), new Mobile($this->faker->phoneNumber()), new Email($this->faker->email()), new Country($this->faker->countryCode()));

        $createCustomerUseCase = new AddOrderPaymentUseCase($customer);
        $this->given(new AddOrderPaymentInput(Guid::from(Uuid::uuid4()), '7617267835t64r3gjhknmb2n2gjk', '55559808908', 'sjjshkjhjk'), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleCustomers())
            ->assertScenario();
    }

    public function test_executing_add_customer_payment_usecase_successfully()
    {
        $customer = Mockery::spy(new CustomerEntity($this->domainEventBus));
        $customer->fromExisting(123, new Name($this->faker->name()), new Mobile($this->faker->phoneNumber()), new Email($this->faker->email()), new Country($this->faker->countryCode()));

        $createCustomerUseCase = new AddOrderPaymentUseCase($customer);
        $this->given(new AddOrderPaymentInput(Guid::from(Uuid::uuid4()), 'pm_101MxKla0m1mnnLKla', '1234', 'cus_ML2kwIibSekBGH'), $createCustomerUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(CustomerOrderPaymentAdded::class)
            ->assertScenario();
    }
}
