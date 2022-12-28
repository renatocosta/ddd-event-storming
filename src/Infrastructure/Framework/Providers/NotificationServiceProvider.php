<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\Notification\NotificationEmailNotifiedEventHandler;
use Application\EventHandlers\Notification\NotificationEmailPublishedEventHandler;
use Application\EventHandlers\Notification\NotificationSmsNotifiedEventHandler;
use Application\EventHandlers\Notification\NotificationSmsPublishedEventHandler;
use Common\Application\Event\Bus\DomainEventBus;
use Application\UseCases\Notification\Email\INotificationEmailPublishUseCase;
use Application\UseCases\Notification\Email\INotificationEmailNotifyUseCase;
use Application\UseCases\Notification\Email\NotificationEmailNotifyUseCase;
use Application\UseCases\Notification\Email\NotificationEmailPublishUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsNotifyUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsNotifyUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsPublishUseCase;
use Domain\Model\Notification\Notification;
use Domain\Model\Notification\NotificationEntity;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class NotificationServiceProvider extends ServiceProvider
{

    public const DEFAULT_PUBLISH_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, Producer::class => Producer::class];

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

        $this->app->bind(NotificationEmailPublishedEventHandler::class, function ($app, $dependencies) {
            return new NotificationEmailPublishedEventHandler($dependencies[Producer::class]);
        });

        $this->app->bind(NotificationEmailNotifiedEventHandler::class, function () {
            return new NotificationEmailNotifiedEventHandler();
        });

        $this->app->bind(NotificationSmsPublishedEventHandler::class, function ($app, $dependencies) {
            return new NotificationSmsPublishedEventHandler($dependencies[Producer::class]);
        });

        $this->app->bind(NotificationSmsNotifiedEventHandler::class, function () {
            return new NotificationSmsNotifiedEventHandler(new Client(env('TWILIO_ACCOUNT_SID', ' '), env('TWILIO_AUTH_TOKEN', ' ')));
        });

        $this->app->bind(Notification::class, function ($app, $dependencies) {
            return new NotificationEntity($dependencies[DomainEventBus::class]);
        });
    }

    public function loadUseCases(): void
    {
        ## Publish an Email notification ##
        $this->app->bind(
            INotificationEmailPublishUseCase::class,
            function ($app, $dependencies) {
                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_PUBLISH_DEPENDENCIES);
                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(NotificationEmailPublishedEventHandler::class, $dependenciesLoader));
                return new NotificationEmailPublishUseCase($app->makeWith(Notification::class, $dependenciesLoader));
            }
        );

        ## Notify an Email notification ##
        $this->app->bind(
            INotificationEmailNotifyUseCase::class,
            function ($app) {

                $domainEventBus = new DomainEventBus();
                $domainEventBus->subscribe($app[NotificationEmailNotifiedEventHandler::class]);

                return new NotificationEmailNotifyUseCase($app->makeWith(Notification::class, [DomainEventBus::class => $domainEventBus]));
            }
        );

        ## Publish a Sms notification ##
        $this->app->bind(
            INotificationSmsPublishUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_PUBLISH_DEPENDENCIES);
                $dependenciesLoader[DomainEventBus::class]->subscribe($app->makeWith(NotificationSmsPublishedEventHandler::class, $dependenciesLoader));

                return new NotificationSmsPublishUseCase($app->makeWith(Notification::class, $dependenciesLoader));
            }
        );

        ## Notify a Sms notification ##
        $this->app->bind(
            INotificationSmsNotifyUseCase::class,
            function ($app) {

                $domainEventBus = new DomainEventBus();
                $domainEventBus->subscribe($app[NotificationSmsNotifiedEventHandler::class]);

                return new NotificationSmsNotifyUseCase($app->makeWith(Notification::class, [DomainEventBus::class => $domainEventBus]));
            }
        );
    }
}
