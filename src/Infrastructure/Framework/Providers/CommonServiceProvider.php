<?php

namespace Infrastructure\Framework\Providers;

use Common\Application\Event\Bus\DomainEventBus;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Specifications\PropertyAddressPreviouslyAssociated;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\Specifications\FollowUpInProgress;
use Domain\Services\IPropertyAddressInUse;
use Domain\Services\OrderTrackable;
use Domain\Services\OrderTracker;
use Domain\Services\ProjectReportsFollowUpCollection;
use Domain\Services\ProjectReportsMaterializable;
use Domain\Services\PropertyAddressInUse;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Proxy\ProxyBnB;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Notifications\Messages\SlackMessage;
use Infrastructure\Repositories\LeadUsersRepository;
use Infrastructure\Repositories\LeadUsersRepositoryInMemory;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Infrastructure\Integration\Events\MixPanelEventHandler;
use Infrastructure\Integration\Events\SlackEventHandler;
use Infrastructure\Repositories\TransactionManagerFake;
use Infrastructure\Framework\Entities\OrderIntegrationEventsModel;
use Infrastructure\Framework\Notifications\SlackOrderNotification;
use Infrastructure\Integration\Events\CloseCrmEventHandler;
use Infrastructure\Integration\Events\DematerializeEntitiesEventHandler;
use Infrastructure\Integration\Events\LinkMinkEventHandler;
use Infrastructure\Proxy\CloseCrmProxy;
use Infrastructure\Proxy\LinkMinkProxy;
use Infrastructure\Proxy\ProxyBnBAuthenticator;
use Infrastructure\Proxy\ProxyedBnB;
use Infrastructure\Repositories\ProjectReportsRepositoryInMemory;
use Mixpanel;
use Mockery;
use Stripe\BaseStripeClient;
use Stripe\StripeClient;

class CommonServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(DomainEventBus::class, function () {
            return new DomainEventBus();
        });

        $this->app->bind(OrderTrackable::class, function ($app, $dependencies) {
            return new OrderTracker($dependencies[IOrderRepository::class]);
        });

        $this->app->bind(ProjectReportsMaterializable::class, function ($app) {
            $projectReportsRepository = new ProjectReportsRepositoryInMemory($app[IProjectReportsRepository::class], $app['cache.store']);
            return new ProjectReportsFollowUpCollection($projectReportsRepository);
        });

        $this->app->bind(ProxyedBnB::class, function ($app) {
            return new ProxyBnBAuthenticator(new ProxyBnB($app[HttpClient::class]), $app[HttpClient::class], $app['cache.store']);
        });

        $this->app->bind(CloseCrmProxy::class, function ($app) {
            return new CloseCrmProxy($app[HttpClient::class]);
        });

        $this->app->bind(LinkMinkProxy::class, function ($app) {
            return new LinkMinkProxy($app[HttpClient::class]);
        });

        $this->app->bind(BaseStripeClient::class, function ($app) {
            return new StripeClient(env('STRIPE_API_KEY'));
        });

        $this->app->bind(TransactionManagerFake::class, function () {
            return new TransactionManagerFake($this->app, Mockery::spy(ConnectionFactory::class));
        });

        $this->app->bind(Mixpanel::class, function () {
            return Mixpanel::getInstance(env('MIXPANEL_TOKEN'));
        });

        $this->app->bind(SlackOrderNotification::class, function () {
            return new SlackOrderNotification(new SlackMessage);
        });

        $this->app->bind(MixPanelEventHandler::class, function ($app, $dependencies) {
            return new MixPanelEventHandler($dependencies[OutboxIntegrationEventsRepository::class]);
        });

        $this->app->bind(SlackEventHandler::class, function ($app, $dependencies) {
            return new SlackEventHandler($dependencies[OutboxIntegrationEventsRepository::class]);
        });

        $this->app->bind(CloseCrmEventHandler::class, function ($app, $dependencies) {
            return new CloseCrmEventHandler($dependencies[OutboxIntegrationEventsRepository::class]);
        });

        $this->app->bind(LinkMinkEventHandler::class, function ($app) {
            return new LinkMinkEventHandler($app[OutboxIntegrationEventsRepository::class]);
        });

        $this->app->bind(DematerializeEntitiesEventHandler::class, function ($app, $dependencies) {
            return new DematerializeEntitiesEventHandler($dependencies[OutboxIntegrationEventsRepository::class] ?? $app[OutboxIntegrationEventsRepository::class]);
        });

        $this->app->bind(OutboxIntegrationEventsRepository::class, function ($app) {
            return new OutboxIntegrationEventsRepository(new OrderIntegrationEventsModel());
        });

        $this->app->bind(LeadUsersRepository::class, function ($app) {
            return new LeadUsersRepositoryInMemory(new LeadUsersRepository(), $app['cache.store']);
        });

        $this->app->bind(IPropertyAddressInUse::class, function ($app, $dependencies) {
            return new PropertyAddressInUse($dependencies[PropertyAddressPreviouslyAssociated::class], $app[FollowUpInProgress::class]);
        });
    }
}
