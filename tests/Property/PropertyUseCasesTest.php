<?php

namespace Tests\Property;

use Application\EventHandlers\Property\PropertyCreatedEventHandler;
use Application\UseCases\Property\Create\CreatePropertyUseCase;
use Application\UseCases\Property\Create\CreatePropertyInput;
use Common\Application\Event\Bus\DomainEventBus;
use Common\Exception\UnableToHandleBusinessRules;
use DG\BypassFinals;
use Domain\Model\Property\Events\PropertyCreated;
use Domain\Model\Property\PropertyEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Repositories\PropertyRepositoryFake;
use Mockery;
use Tests\Utilities\AggregateRootTestCase;

class PropertyUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private $repository;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();

        $this->repository = new PropertyRepositoryFake();

        $createEventHandler = \Mockery::spy(new PropertyCreatedEventHandler($this->repository));
        $createEventHandler->shouldReceive('handle');
        $this->domainEventBus = new DomainEventBus();
        $this->domainEventBus->subscribe($createEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_create_property_use_case_for_invalid_input()
    {
        $property = Mockery::spy(new PropertyEntity($this->domainEventBus));

        $createPropertyUseCase = new CreatePropertyUseCase($property);
        $lat = $this->faker->unique()->randomElement(array(-91.00, -98.02));
        $lon = $this->faker->unique()->randomElement(array(-120.00, 190.0));
        $this->given(new CreatePropertyInput(12233, $this->faker->address(), '08221233', 'Hawaii', '1222', 'Honolulu', 'ssss', '122', '12', $lat, $lon), $createPropertyUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->thenNothingShouldHaveHappened()
            ->expectToFail(new UnableToHandleBusinessRules())
            ->assertScenario();
    }

    public function test_executing_a_create_property_usecase_successfully()
    {
        $property = Mockery::spy(new PropertyEntity($this->domainEventBus));

        $createPropertyUseCase = new CreatePropertyUseCase($property);
        $lat = $this->faker->unique()->randomElement(array(-91.00, -98.02));
        $lon = $this->faker->unique()->randomElement(array(-120.00, 190.0));
        $this->given(new CreatePropertyInput(12233, $this->faker->address(), '08221233', 'Hawaii', '1222', 'Honolulu', 'ssss', '122', '12', $this->faker->latitude(), $this->faker->longitude()), $createPropertyUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(PropertyCreated::class)
            ->assertScenario();
    }
}
