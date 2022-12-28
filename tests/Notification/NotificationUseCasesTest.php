<?php

namespace Tests\Notification;

use Application\EventHandlers\Notification\NotificationEmailNotifiedEventHandler;
use Application\EventHandlers\Notification\NotificationSmsNotifiedEventHandler;
use Application\UseCases\Notification\Email\NotificationEmailInput;
use Application\UseCases\Notification\Email\NotificationEmailNotifyUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsNotifyUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsInput;
use Common\Application\Event\Bus\DomainEventBus;
use DG\BypassFinals;
use Domain\Model\Notification\Events\NotificationEmailNotified;
use Domain\Model\Notification\Events\NotificationSmsNotified;
use Domain\Model\Notification\NotificationEntity;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Utilities\AggregateRootTestCase;

class NotificationUseCasesTest extends AggregateRootTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    public function setUp(): void
    {
        $this->setUpAggregateRoot();
        parent::setUp();
        $notificationSmsNotifiedEventHandler = Mockery::spy($this->app[NotificationSmsNotifiedEventHandler::class]);
        $notificationSmsNotifiedEventHandler->shouldReceive('handle');
        $notificationEmailNotifiedEventHandler = Mockery::spy($this->app[NotificationEmailNotifiedEventHandler::class]);
        $notificationEmailNotifiedEventHandler->shouldReceive('handle');
        $this->domainEventBus = $this->faker->eventBus($notificationSmsNotifiedEventHandler, $notificationEmailNotifiedEventHandler);
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_executing_a_notify_sms_usecase_successfully()
    {
        $notification = Mockery::spy(new NotificationEntity($this->domainEventBus));
        $notificationSmsNotifyUseCase = new NotificationSmsNotifyUseCase($notification);
        $this->given(new NotificationSmsInput($this->faker->phoneNumber, []), $notificationSmsNotifyUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(NotificationSmsNotified::class);
        $this->assertScenario();
    }

    public function test_executing_a_notify_email_usecase_successfully()
    {
        $notification = Mockery::spy(new NotificationEntity($this->domainEventBus));
        $notificationEmailNotifyUseCase = new NotificationEmailNotifyUseCase($notification);
        $this->given(new NotificationEmailInput($this->faker->email, $this->faker->text(200)), $notificationEmailNotifyUseCase)
            ->when(...$this->domainEventBus->getRaisedEvents())
            ->then(NotificationEmailNotified::class);
        $this->assertScenario();
    }
}
