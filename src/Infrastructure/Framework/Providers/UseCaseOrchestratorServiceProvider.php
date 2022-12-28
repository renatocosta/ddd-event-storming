<?php

namespace Infrastructure\Framework\Providers;

use Application\Orchestration\ChangeProjectReportsOrchestrator;
use Application\Orchestration\ConfirmOrderOrchestrator;
use Application\Orchestration\CreateOrderOrchestrator;
use Application\UseCases\Customer\Create\ICreateCustomerUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\IAddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\IAddOrderPaymentUseCase;
use Application\UseCases\Notification\Email\INotificationEmailPublishUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Order\AssignProject\IAssignProjectUseCase;
use Application\UseCases\Order\Confirm\IConfirmOrderUseCase;
use Application\UseCases\Order\Create\ICreateOrderUseCase;
use Application\UseCases\ProjectReports\Change\IChangeProjectReportsUseCase;
use Application\UseCases\Property\Create\ICreatePropertyUseCase;
use Application\UseCases\User\AssignCustomer\IAssignCustomerUseCase;
use Application\UseCases\User\Authenticate\IAuthenticateUserUseCase;
use Application\UseCases\User\Identify\IIdentifyUserUseCase;
use Application\UseCases\User\Queries\IGetUserCustomerQuery;
use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\ICustomerRepository;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Domain\Model\Notification\Events\NotificationSmsPublished;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\MessageReport\MessageRecipientable;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\Property\IPropertyRepository;
use Domain\Model\Property\Property;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Domain\Services\OrderTrackable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Stripe\BaseStripeClient;
use YlsIdeas\FeatureFlags\Facades\Features;

class UseCaseOrchestratorServiceProvider extends ServiceProvider
{

    public const DEFAULT_CREATE_ORDER_ORCHESTRATION_DEPENDENCIES = [DatabaseManager::class => 'db', DomainEventBus::class => DomainEventBus::class, Order::class => Order::class, IOrderRepository::class => IOrderRepository::class, User::class => User::class, Customer::class => Customer::class, ICustomerRepository::class => ICustomerRepository::class, IUserRepository::class => IUserRepository::class, Property::class => Property::class, IPropertyRepository::class => IPropertyRepository::class, Features::class => Features::class, Producer::class => Producer::class, BaseStripeClient::class => BaseStripeClient::class, OutboxIntegrationEventsRepository::class => OutboxIntegrationEventsRepository::class];

    public const DEFAULT_CONFIRM_ORDER_ORCHESTRATION_DEPENDENCIES = [DatabaseManager::class => 'db', DomainEventBus::class => DomainEventBus::class, Order::class => Order::class, IOrderRepository::class => IOrderRepository::class, User::class => User::class, IUserRepository::class => IUserRepository::class, Features::class => Features::class, OutboxIntegrationEventsRepository::class => OutboxIntegrationEventsRepository::class];

    public const DEFAULT_CHANGE_PROJECT_ORCHESTRATION_DEPENDENCIES = [DatabaseManager::class => 'db', DomainEventBus::class => DomainEventBus::class, ProjectReports::class => ProjectReports::class, IProjectReportsRepository::class => IProjectReportsRepository::class, Features::class => Features::class, IUserRepository::class => IUserRepository::class, OutboxIntegrationEventsRepository::class => OutboxIntegrationEventsRepository::class, IOrderRepository::class => IOrderRepository::class, Order::class => Order::class, User::class => User::class, MessageRecipientable::class => MessageRecipientable::class];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CreateOrderOrchestrator::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CREATE_ORDER_ORCHESTRATION_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->startIntegrationHandlerAfter(NotificationSmsPublished::class);

                return new CreateOrderOrchestrator(
                    $dependenciesLoader[DatabaseManager::class],
                    $app->makeWith(ICreateCustomerUseCase::class, $dependenciesLoader),
                    $app->makeWith(ICreatePropertyUseCase::class, $dependenciesLoader),
                    $app->makeWith(ICreateOrderUseCase::class, $dependenciesLoader),
                    $app->makeWith(IAddOrderLocationUseCase::class, $dependenciesLoader),
                    $app->makeWith(IIdentifyUserUseCase::class, $dependenciesLoader),
                    $app->makeWith(IAddOrderPaymentUseCase::class, $dependenciesLoader),
                    $app->makeWith(INotificationSmsPublishUseCase::class, $dependenciesLoader),
                    $dependenciesLoader[Order::class],
                    $dependenciesLoader[User::class],
                    $dependenciesLoader[Customer::class]
                );
            }
        );

        $this->app->bind(
            ConfirmOrderOrchestrator::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CONFIRM_ORDER_ORCHESTRATION_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->startIntegrationHandlerAfter(NotificationSmsPublished::class);

                return new ConfirmOrderOrchestrator(
                    $dependenciesLoader[DatabaseManager::class],
                    $app->makeWith(IConfirmOrderUseCase::class, $dependenciesLoader),
                    $app->makeWith(IAuthenticateUserUseCase::class, $dependenciesLoader),
                    $app->makeWith(INotificationEmailPublishUseCase::class, $dependenciesLoader),
                    $app->makeWith(INotificationSmsPublishUseCase::class, $dependenciesLoader),
                    $dependenciesLoader[Order::class],
                    $dependenciesLoader[User::class]
                );
            }
        );

        $this->app->bind(
            ChangeProjectReportsOrchestrator::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_CHANGE_PROJECT_ORCHESTRATION_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->startIntegrationHandlerAfter(NotificationSmsPublished::class);

                $orderTrackable = $app->makeWith(OrderTrackable::class, [IOrderRepository::class => $dependenciesLoader[IOrderRepository::class]]);
                $dependenciesLoader[OrderTrackable::class] = $orderTrackable;

                return new ChangeProjectReportsOrchestrator(
                    $dependenciesLoader[DatabaseManager::class],
                    $app->makeWith(IChangeProjectReportsUseCase::class, $dependenciesLoader),
                    $app->makeWith(INotificationEmailPublishUseCase::class, $dependenciesLoader),
                    $app->makeWith(INotificationSmsPublishUseCase::class, $dependenciesLoader),
                    $app->makeWith(IGetUserCustomerQuery::class, $dependenciesLoader),
                    $app->makeWith(IAssignCustomerUseCase::class, $dependenciesLoader),
                    $app->makeWith(IAssignProjectUseCase::class, $dependenciesLoader),
                    $orderTrackable,
                    $dependenciesLoader[ProjectReports::class],
                    $dependenciesLoader[MessageRecipientable::class]
                );
            }
        );
    }
}
