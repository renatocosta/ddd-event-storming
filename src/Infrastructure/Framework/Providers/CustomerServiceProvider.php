<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\Customer\CustomerCreatedEventHandler;
use Application\EventHandlers\Customer\CustomerOrderLocationAddedEventHandler;
use Application\EventHandlers\Customer\CustomerOrderPaymentAddedEventHandler;
use Application\EventHandlers\Customer\CustomerUpdatedEventHandler;
use Application\UseCases\Customer\Create\CreateCustomerUseCase;
use Application\UseCases\Customer\Create\ICreateCustomerUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\AddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\IAddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\AddOrderPaymentUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\IAddOrderPaymentUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\CustomerEntity;
use Domain\Model\Customer\ICustomerRepository;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Framework\Entities\CustomerOrderLocationsModel;
use Infrastructure\Framework\Entities\CustomerOrderPaymentsModel;
use Infrastructure\Framework\Entities\CustomersModel;
use Infrastructure\Repositories\CustomerRepository;

class CustomerServiceProvider extends ServiceProvider
{

    public const DEFAULT_CUSTOMER_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, Customer::class => Customer::class, ICustomerRepository::class => ICustomerRepository::class];

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

        $this->app->bind(ICustomerRepository::class, function ($app) {
            $customerModelWriter = new CustomersModel();
            $customerModelWriter->on('mysql::write');
            $customerModelReader = new CustomersModel();
            $customerModelReader->on('mysql::read');
            $customerOrderPaymentsModelWriter = new CustomerOrderPaymentsModel();
            $customerOrderPaymentsModelWriter->on('mysql::write');
            $customerOrderLocationsModelWriter = new CustomerOrderLocationsModel();
            $customerOrderLocationsModelWriter->on('mysql::write');
            return new CustomerRepository($customerModelWriter, $customerModelReader, $customerOrderPaymentsModelWriter, $customerOrderLocationsModelWriter);
        });

        $this->app->bind(CustomerCreatedEventHandler::class, function ($app, $dependencies) {
            return new CustomerCreatedEventHandler($dependencies[ICustomerRepository::class]);
        });

        $this->app->bind(CustomerUpdatedEventHandler::class, function ($app) {
            return new CustomerUpdatedEventHandler($app[ICustomerRepository::class]);
        });

        $this->app->bind(CustomerOrderLocationAddedEventHandler::class, function ($app, $dependencies) {
            return new CustomerOrderLocationAddedEventHandler($dependencies[ICustomerRepository::class]);
        });

        $this->app->bind(CustomerOrderPaymentAddedEventHandler::class, function ($app, $dependencies) {
            return new CustomerOrderPaymentAddedEventHandler($dependencies[ICustomerRepository::class]);
        });

        $this->app->bind(Customer::class, function ($app, $dependencies) {
            return new CustomerEntity($dependencies[DomainEventBus::class]);
        });
    }

    public function loadUseCases(): void
    {
        ## Create a Customer ##
        $this->app->bind(
            ICreateCustomerUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CUSTOMER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CustomerCreatedEventHandler::class, [ICustomerRepository::class => $dependenciesLoader[ICustomerRepository::class]]));

                return new CreateCustomerUseCase($dependenciesLoader[Customer::class]);
            }
        );

        ## Add order with location to customer ##
        $this->app->bind(
            IAddOrderLocationUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CUSTOMER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CustomerOrderLocationAddedEventHandler::class, [ICustomerRepository::class => $dependenciesLoader[ICustomerRepository::class]]));

                return new AddOrderLocationUseCase($dependenciesLoader[Customer::class]);
            }
        );

        ## Add order with payment to customer ##
        $this->app->bind(
            IAddOrderPaymentUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CUSTOMER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CustomerOrderPaymentAddedEventHandler::class, [ICustomerRepository::class => $dependenciesLoader[ICustomerRepository::class]]));

                return new AddOrderPaymentUseCase($dependenciesLoader[Customer::class]);
            }
        );
    }
}
