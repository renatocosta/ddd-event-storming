<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\ProjectReports\ProjectReportsChangedEventHandler;
use Application\EventHandlers\ProjectReports\ProjectReportsMessageRecipientReadyEventHandler;
use Application\EventHandlers\ProjectReports\ProjectReportsReplicatedEventHandler;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsUseCase;
use Application\UseCases\ProjectReports\Change\IChangeProjectReportsUseCase;
use Application\UseCases\ProjectReports\Queries\GetProjectReportsFollowUpQuery;
use Application\UseCases\ProjectReports\Queries\GetProjectReportsQuery;
use Application\UseCases\ProjectReports\Queries\GetProjectReportsStateQuery;
use Application\UseCases\ProjectReports\Queries\IGetProjectReportsFollowUpQuery;
use Application\UseCases\ProjectReports\Replicate\IReplicateProjectReportsUseCase;
use Application\UseCases\ProjectReports\Replicate\ReplicateProjectReportsUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Order\Order;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\MessageReport\MessageRecipient;
use Domain\Model\ProjectReports\MessageReport\MessageRecipientable;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\Specifications\FollowUpInProgress;
use Domain\Model\ProjectReports\Specifications\PendingFollowUp;
use Domain\Model\User\User;
use Domain\Services\OrderTrackable;
use Domain\Services\ProjectReportsMaterializable;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integration\Events\MixPanelEventHandler;
use Infrastructure\Integration\Events\SlackEventHandler;
use Infrastructure\Integration\Events\CloseCrmEventHandler;
use Infrastructure\Integration\Events\DematerializeEntitiesEventHandler;
use Infrastructure\Integration\Events\LinkMinkEventHandler;
use YlsIdeas\FeatureFlags\Facades\Features;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Illuminate\Contracts\Cache\Repository as Cache;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Infrastructure\Repositories\ProjectReportsRepository;
use Infrastructure\Repositories\ProjectReportsRepositoryInMemory;
use stdClass;

class ProjectReportsServiceProvider extends ServiceProvider
{

    public const DEFAULT_PROJECT_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, ProjectReports::class => ProjectReports::class, Order::class => Order::class, User::class => User::class, Cache::class => 'cache.store', IProjectReportsRepository::class => IProjectReportsRepository::class, Features::class => Features::class, Producer::class => Producer::class, OutboxIntegrationEventsRepository::class => OutboxIntegrationEventsRepository::class, OrderTrackable::class => OrderTrackable::class, MessageRecipientable::class => MessageRecipientable::class];

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

        $this->app->bind(IProjectReportsRepository::class, function ($app) {
            return new ProjectReportsRepository();
        });

        $this->app->bind(ProjectReportsChangedEventHandler::class, function ($app, $dependencies) {
            $projectRepository = new ProjectReportsRepositoryInMemory($dependencies[IProjectReportsRepository::class], $dependencies[Cache::class]);
            return new ProjectReportsChangedEventHandler($projectRepository, $dependencies[OrderTrackable::class]);
        });

        $this->app->bind(ProjectReportsMessageRecipientReadyEventHandler::class, function ($app, $dependencies) {
            return new ProjectReportsMessageRecipientReadyEventHandler($dependencies[MessageRecipientable::class], $dependencies[OrderTrackable::class]);
        });

        $this->app->bind(ProjectReportsReplicatedEventHandler::class, function ($app) {
            $projectRepository = new ProjectReportsRepositoryInMemory($app[IProjectReportsRepository::class], $app['cache.store']);
            return new ProjectReportsReplicatedEventHandler($projectRepository);
        });

        $this->app->bind(ProjectReports::class, function ($app, $dependencies) {
            return new ProjectReportsEntity($dependencies[DomainEventBus::class]);
        });

        $this->app->bind(FollowUpInProgress::class, function ($app) {
            return new FollowUpInProgress($app[IProjectReportsRepository::class]);
        });

        $this->app->bind(PendingFollowUp::class, function ($app) {
            return new PendingFollowUp($app[IProjectReportsRepository::class]);
        });

        $this->app->bind(MessageRecipientable::class, function ($app, $dependencies) {

            $data = new stdClass;
            $data->step = [];
            $data->channels = [];
            $data->destination = [];
            $data->magicLink = [];
            $data->body = [];
            $data->order = $dependencies[Order::class];

            return new MessageRecipient($data);
        });
    }

    public function loadUseCases(): void
    {
        ## Create a Project report ##
        $this->app->bind(
            IChangeProjectReportsUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_PROJECT_DEPENDENCIES);

                $orderTrackable = $dependenciesLoader[OrderTrackable::class];

                $dependenciesLoader[DomainEventBus::class]->subscribers(
                    $app->makeWith(ProjectReportsChangedEventHandler::class, [IProjectReportsRepository::class => $dependenciesLoader[IProjectReportsRepository::class], Cache::class => $dependenciesLoader[Cache::class], User::class => $dependenciesLoader[User::class], OrderTrackable::class => $orderTrackable]),
                    $app->makeWith(ProjectReportsMessageRecipientReadyEventHandler::class, [MessageRecipientable::class => $dependenciesLoader[MessageRecipientable::class], OrderTrackable::class => $orderTrackable]),
                    $app->makeWith(SlackEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]),
                    $app->makeWith(MixPanelEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]),
                    $app->makeWith(DematerializeEntitiesEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]])
                );

                if ($dependenciesLoader[Features::class]::accessible('close_crm')) {
                    $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(CloseCrmEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]));
                }

                if ($dependenciesLoader[Features::class]::accessible('linkmink')) {
                    $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(LinkMinkEventHandler::class, [OutboxIntegrationEventsRepository::class => $dependenciesLoader[OutboxIntegrationEventsRepository::class]]));
                }

                $projectReportsRepository = new ProjectReportsRepositoryInMemory($dependenciesLoader[IProjectReportsRepository::class], $dependenciesLoader[Cache::class]);

                return new ChangeProjectReportsUseCase($dependenciesLoader[ProjectReports::class], $projectReportsRepository, $orderTrackable);
            }
        );

        ## Replicate a Project report ##
        $this->app->bind(
            IReplicateProjectReportsUseCase::class,
            function ($app) {
                $domainEventBus = new DomainEventBus();
                $domainEventBus->subscribers($app[ProjectReportsReplicatedEventHandler::class]);
                return new ReplicateProjectReportsUseCase(new ProjectReportsEntity($domainEventBus), $app[IProjectReportsRepository::class],  $app[OrderTrackable::class]);
            }
        );

        ## Get project by order number ##
        $this->app->bind(
            IGetProjectQuery::class,
            function ($app) {
                $projectRepository = new ProjectReportsRepositoryInMemory($app[IProjectReportsRepository::class], $app['cache.store']);
                return new GetProjectReportsQuery(new ProjectReportsEntity($app[DomainEventBus::class]), $projectRepository);
            }
        );

        ## Get project current state by order number ##
        $this->app->bind(
            IGetProjectStateQuery::class,
            function ($app) {
                $projectRepository = new ProjectReportsRepositoryInMemory($app[IProjectReportsRepository::class], $app['cache.store']);
                return new GetProjectReportsStateQuery(new ProjectReportsEntity($app[DomainEventBus::class]), $projectRepository);
            }
        );

        ## Get project reported by order id ##
        $this->app->bind(
            IGetProjectReportQuery::class,
            function ($app) {
                $projectRepository = new ProjectReportsRepositoryInMemory($app[IProjectReportsRepository::class], $app['cache.store']);
                return new GetProjectReportsQuery(new ProjectReportsEntity($app[DomainEventBus::class]), $projectRepository);
            }
        );

        ## Get project follow up ##
        $this->app->bind(
            IGetProjectReportsFollowUpQuery::class,
            function ($app) {
                return new GetProjectReportsFollowUpQuery($app[ProjectReportsMaterializable::class]);
            }
        );
    }
}
