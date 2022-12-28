<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\ProjectReports\ProjectReportsChangedEventHandler;
use Application\UseCases\Order\AssignProject\AssignProjectUseCase;
use Application\UseCases\Order\AssignProject\IAssignProjectUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\Order;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReportsEntity;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\State\ProjectReportsTransition;
use Faker\Provider\Base;
use Infrastructure\Repositories\OrderRepositoryFake;
use Mixpanel;
use Faker\Generator;
use Infrastructure\Repositories\ProjectReportsRepositoryFake;

class ProjectReportsFakerProvider extends Base
{

    public function __construct(Generator $generator, private OrderFakerProvider $orderFakerProvider, private UserFakerProvider $userFakerProvider)
    {
        parent::__construct($generator);
    }

    public function invalidProjectReportsStatus()
    {
        return static::randomElement(['OrderCreated', -12, 'sssa', 'A-122']);
    }

    public function payloadProjectReports(string $evenType, string $entityType)
    {
        return Payload::from([
            'event_type' => $evenType,
            'entity_type' => $entityType,
            'version' => 1,
            'event_data' =>
            [
                'cleaner' => ['id' => 1, 'name' => 'sss', 'report_text' => 'My cleaning report', 'price_per_hour' => '122', 'phone' => '222528'],
                'recipient' => ['channel' => 'sms', 'phone_number' => '5511952358134', 'name' => 'John', 'report_text' => 'Lorem ipsum'],
                'customer' => [
                    'payment' => ['card_number_last4' => '1234', 'stripe_id' => '287892789'],
                    'property' => ['id' => 1123, 'address' => 'A lks', 'zipcode' => '977297', 'extra_details' => 'ss', 'number_of_bedrooms' => 12, 'number_of_bathrooms' => 1, 'size' => 10],
                    'personal_information' => ['name' => 'ghfefegf', 'phone_number' => '5511954358134', 'email' => 'renatocostahome@gmail.com'],
                    'location' => ['timezone' => 'UTC'],
                    'id' => 23
                ],
                'project' => ['id' => 111, 'start_date' => 1648825200, 'cancellation_date' => 1648825200, 'cancellation_reason' => 'ProjectCancelledCreditCardAuthFailed'],
                'affiliate_trackable' => 'sjkshksjhjkhk',
                'request' => ['name' => 'JOhn b', 'report_text' => 'LOrem ipsum', 'channel' => 'email', 'email' => 'xmnbxx@ds.com']
            ]
        ]);
    }

    public function invalidProjectReportsPayload()
    {
        return 'lksj';
    }

    public function projectReports()
    {
        return \Mockery::spy(new ProjectReportsEntity(new DomainEventBus));
    }

    public function projectReportsStatus()
    {
        return \Mockery::spy(ProjectReportsStatus::class);
    }

    public function projectReportsTransition()
    {
        return \Mockery::mock(ProjectReportsTransition::class);
    }

    public function projectReportsRepository(): IProjectReportsRepository
    {
        return new ProjectReportsRepositoryFake($this);
    }

    public function mixPanel()
    {
        $mixPanel = \Mockery::mock(Mixpanel::class);
        $mixPanel->shouldReceive('identify');
        $mixPanel->shouldReceive('track');
        $mixPanel->shouldReceive('flush');
        return $mixPanel;
    }

    public function changeProjectReportsEventHandler($app, $order, $projectReports, $orderTracker)
    {

        $changeEventHandler = new ProjectReportsChangedEventHandler($this->projectReportsRepository(), $orderTracker);
        $changeEventHandler = \Mockery::spy($changeEventHandler);
        $changeEventHandler->shouldReceive('handle');
        return $changeEventHandler;
    }

    public function assignProjectUseCase($app): IAssignProjectUseCase
    {
        $domainEventBus = new DomainEventBus;

        return new AssignProjectUseCase($app->makeWith(Order::class, [DomainEventBus::class => $domainEventBus]), new OrderRepositoryFake($this->orderFakerProvider));
    }
}
