<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\Order\AssignProjectEventHandler;
use Application\EventHandlers\Order\OrderConfirmedEventHandler;
use Application\EventHandlers\Order\OrderCreatedEventHandler;
use Application\EventHandlers\Order\OrderUpdatedEventHandler;
use Application\EventHandlers\Order\ReviewProjectEventHandler;
use Application\EventHandlers\Order\SendRatingEventHandler;
use Application\EventHandlers\Order\SendTipEventHandler;
use Application\UseCases\Order\AddMoreCleaners\AddMoreCleanersUseCase;
use Application\UseCases\Order\AddMoreCleaners\IAddMoreCleanersUseCase;
use Application\UseCases\Order\AssignProject\AssignProjectUseCase;
use Application\UseCases\Order\AssignProject\IAssignProjectUseCase;
use Application\UseCases\Order\Confirm\ConfirmOrderUseCase;
use Application\UseCases\Order\Confirm\IConfirmOrderUseCase;
use Application\UseCases\Order\Create\CreateOrderUseCase;
use Application\UseCases\Order\Create\ICreateOrderUseCase;
use Application\UseCases\Order\Queries\GetAddressAvailabilityQuery;
use Application\UseCases\Order\Queries\GetOrderQuery;
use Application\UseCases\Order\Queries\IGetAddressAvailabilityQuery;
use Application\UseCases\Order\Queries\IGetOrderQuery;
use Application\UseCases\Order\ReviewProject\IReviewProjectUseCase;
use Application\UseCases\Order\ReviewProject\ReviewProjectUseCase;
use Application\UseCases\Order\SendRating\ISendRatingUseCase;
use Application\UseCases\Order\SendRating\SendRatingUseCase;
use Application\UseCases\Order\SendTip\ISendTipUseCase;
use Application\UseCases\Order\SendTip\SendTipUseCase;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\Specifications\PropertyAddressPreviouslyAssociated;
use Domain\Model\ProjectReports\Specifications\PendingFollowUp;
use Domain\Services\IPropertyAddressInUse;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integration\Events\SlackEventHandler;
use Infrastructure\Repositories\OrderRepository;
use Infrastructure\Integration\Events\CloseCrmEventHandler;
use Infrastructure\Integration\Events\DematerializeEntitiesEventHandler;
use Infrastructure\Proxy\ProxyedBnB;
use YlsIdeas\FeatureFlags\Facades\Features;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Infrastructure\Framework\Entities\OrderModel;
use Infrastructure\Integration\Events\OrderConfirmedStreamerEventHandler;
use Infrastructure\Repositories\OrderRepositoryInMemory;

class OrderServiceProvider extends ServiceProvider
{

    public const DEFAULT_ORDER_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, Order::class => Order::class, IOrderRepository::class => IOrderRepository::class, Features::class => Features::class, Producer::class => Producer::class, OutboxIntegrationEventsRepository::class => OutboxIntegrationEventsRepository::class];

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

        $this->app->bind(IOrderRepository::class, function ($app) {
            $orderModelWriter = new OrderModel();
            $orderModelWriter->on('mysql::write');
            $orderModelReader = new OrderModel();
            $orderModelReader->on('mysql::read');
            return new OrderRepositoryInMemory(new OrderRepository($orderModelWriter, $orderModelReader), $app['cache.store']);
        });

        $this->app->bind(OrderCreatedEventHandler::class, function ($app, $dependencies) {
            return new OrderCreatedEventHandler($dependencies[IOrderRepository::class]);
        });

        $this->app->bind(OrderConfirmedEventHandler::class, function ($app, $dependencies) {
            return new OrderConfirmedEventHandler($dependencies[IOrderRepository::class]);
        });

        $this->app->bind(OrderConfirmedStreamerEventHandler::class, function ($app, $dependencies) {
            return new OrderConfirmedStreamerEventHandler($dependencies[Producer::class]);
        });

        $this->app->bind(OrderUpdatedEventHandler::class, function ($app) {
            return new OrderUpdatedEventHandler($app[IOrderRepository::class], $app[Producer::class], $app[DematerializeEntitiesEventHandler::class]);
        });

        $this->app->bind(SendRatingEventHandler::class, function ($app, $dependencies) {
            return new SendRatingEventHandler($dependencies[ProxyedBnB::class], $app[IReviewProjectUseCase::class]);
        });

        $this->app->bind(SendTipEventHandler::class, function ($app, $dependencies) {
            return new SendTipEventHandler($dependencies[ProxyedBnB::class]);
        });

        $this->app->bind(Order::class, function ($app, $dependencies) {
            return new OrderEntity($dependencies[DomainEventBus::class]);
        });

        $this->app->bind(AssignProjectEventHandler::class, function ($app, $dependencies) {
            return new AssignProjectEventHandler($dependencies[IOrderRepository::class]);
        });

        $this->app->bind(PropertyAddressPreviouslyAssociated::class, function ($app, $dependencies) {
            return new PropertyAddressPreviouslyAssociated($app[IOrderRepository::class], $dependencies[Order::class]);
        });
    }

    public function loadUseCases(): void
    {
        ## Create an Order ##
        $this->app->bind(
            ICreateOrderUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_ORDER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribers(
                    $app->makeWith(OrderCreatedEventHandler::class, [IOrderRepository::class => $dependenciesLoader[IOrderRepository::class]]),
                    $app->makeWith(DematerializeEntitiesEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]])
                );

                if ($dependenciesLoader[Features::class]::accessible('close_crm')) {
                    $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CloseCrmEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]));
                }

                return new CreateOrderUseCase($dependenciesLoader[Order::class]);
            }
        );

        ## Confirm an Order ##
        $this->app->bind(
            IConfirmOrderUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_ORDER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribers(
                    $app->makeWith(OrderConfirmedEventHandler::class, [IOrderRepository::class => $dependenciesLoader[IOrderRepository::class]]),
                    $app->makeWith(OrderConfirmedStreamerEventHandler::class, [Producer::class => $dependenciesLoader[Producer::class]]),
                    $app->makeWith(SlackEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]])
                );

                if ($dependenciesLoader[Features::class]::accessible('close_crm')) {
                    $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CloseCrmEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]));
                }

                return new ConfirmOrderUseCase($dependenciesLoader[Order::class], $dependenciesLoader[IOrderRepository::class]);
            }
        );

        ## Add More Cleaners to Order ##
        $this->app->bind(
            IAddMoreCleanersUseCase::class,
            function ($app) {
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app[OrderUpdatedEventHandler::class]);
                $orderEntity = new OrderEntity($domainEventBus);
                return new AddMoreCleanersUseCase($orderEntity, $app[IOrderRepository::class], $app[PendingFollowUp::class]);
            }
        );

        ## Get order by id ##
        $this->app->bind(
            IGetOrderQuery::class,
            function ($app) {
                return new GetOrderQuery(new OrderEntity($app[DomainEventBus::class]), $app[IOrderRepository::class]);
            }
        );

        ## Assign a Project ##
        $this->app->bind(
            IAssignProjectUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_ORDER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribers(
                    $app->makeWith(AssignProjectEventHandler::class, [IOrderRepository::class => $dependenciesLoader[IOrderRepository::class]])
                );

                return new AssignProjectUseCase($dependenciesLoader[Order::class], $dependenciesLoader[IOrderRepository::class]);
            }
        );

        ## Send Rating ##
        $this->app->bind(
            ISendRatingUseCase::class,
            function ($app) {
                $proxyBnb = $app[ProxyedBnB::class];
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app->makeWith(SendRatingEventHandler::class, [ProxyedBnB::class => $proxyBnb]));
                return new SendRatingUseCase(new OrderEntity($domainEventBus), $app[IOrderRepository::class], $proxyBnb);
            }
        );

        ## Send Tip ##
        $this->app->bind(
            ISendTipUseCase::class,
            function ($app) {
                $proxyBnb = $app[ProxyedBnB::class];
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app->makeWith(SendTipEventHandler::class, [ProxyedBnB::class => $proxyBnb]));
                return new SendTipUseCase(new OrderEntity($domainEventBus), $app[IOrderRepository::class], $proxyBnb);
            }
        );

        ## Finish a project review ##
        $this->app->bind(
            IReviewProjectUseCase::class,
            function ($app) {
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app[ReviewProjectEventHandler::class]);
                return new ReviewProjectUseCase(new OrderEntity($domainEventBus), $app[IOrderRepository::class]);
            }
        );

        ## Finish a project review ##
        $this->app->bind(
            IGetAddressAvailabilityQuery::class,
            function ($app) {
                $orderEntity = new OrderEntity($app[DomainEventBus::class]);
                $propertyAddressInUse = $app->makeWith(PropertyAddressPreviouslyAssociated::class, [Order::class => $orderEntity]);
                return new GetAddressAvailabilityQuery($app->makeWith(IPropertyAddressInUse::class, [PropertyAddressPreviouslyAssociated::class => $propertyAddressInUse]), $orderEntity);
            }
        );
    }
}
