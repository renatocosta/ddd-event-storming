<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\Property\PropertyCreatedEventHandler;
use Application\UseCases\Property\Create\CreatePropertyUseCase;
use Application\UseCases\Property\Create\ICreatePropertyUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Property\IPropertyRepository;
use Domain\Model\Property\Property;
use Domain\Model\Property\PropertyEntity;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Framework\Entities\PropertiesModel;
use Infrastructure\Repositories\PropertyRepository;

class PropertyServiceProvider extends ServiceProvider
{

    public const DEFAULT_PROPERTY_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, Property::class => Property::class, PropertyRepository::class => IPropertyRepository::class];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadDependencies();
        $this->loadUseCases();
    }

    public function loadDependencies(): void
    {

        $this->app->bind(IPropertyRepository::class, function ($app) {
            $propertyModelWriter = new PropertiesModel();
            $propertyModelWriter->on('mysql::write');
            $propertyModelReader = new PropertiesModel();
            $propertyModelReader->on('mysql::read');
            return new PropertyRepository($propertyModelWriter, $propertyModelReader);
        });

        $this->app->bind(PropertyCreatedEventHandler::class, function ($app, $dependencies) {
            return new PropertyCreatedEventHandler($dependencies[IPropertyRepository::class]);
        });

        $this->app->bind(Property::class, function ($app, $dependencies) {
            return new PropertyEntity($dependencies[DomainEventBus::class]);
        });
    }

    public function loadUseCases(): void
    {
        ## Create a Customer ##
        $this->app->bind(
            ICreatePropertyUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_PROPERTY_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(PropertyCreatedEventHandler::class, [IPropertyRepository::class => $dependenciesLoader[IPropertyRepository::class]]));

                return new CreatePropertyUseCase($dependenciesLoader[Property::class]);
            }
        );
    }
}
