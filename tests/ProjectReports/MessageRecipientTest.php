<?php

namespace Tests\ProjectReports;

use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use DG\BypassFinals;
use Domain\Model\Order\OrderStatus;
use Domain\Model\ProjectReports\MessageReport\BodyPart;
use Domain\Model\ProjectReports\MessageReport\ChannelsPart;
use Domain\Model\ProjectReports\MessageReport\DestinationPart;
use Domain\Model\ProjectReports\MessageReport\MagicLinkPart;
use Domain\Model\ProjectReports\MessageReport\MessagePart;
use Domain\Model\ProjectReports\MessageReport\MessageRecipient;
use Domain\Model\ProjectReports\MessageReport\StepPart;
use Domain\Services\OrderTrackable;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;
use stdClass;
use Tests\Utilities\TestCase as UtilitiesTestCase;

class MessageRecipientTest extends UtilitiesTestCase
{

    private DomainEventBus $domainEventBus;

    use WithFaker;

    private OrderTrackable $orderTracker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    /**
     * @testWith ["OrderAccepted", "Order"]
     *           ["ProjectStarted", "Project"]
     *           ["ProjectFinished", "Project"]
     *           ["CreditCardAuthFailed", "Project"]
     *           ["CleaningComingUpNotified", "Project"]
     *           ["CleanerUpdatedTheCleaning", "Project"]
     *           ["CreditCardAuthSucceded", "Project"]
     *           ["ProjectCancelled", "Project"]
     *           ["ProjectReported", "Project"]   
     */
    public function test_fail_to_outcome_for_invalid_payload($eventType, $entityType)
    {
        $this->expectException(Exception::class);

        $order = $this->faker->order();
        $order->fromExisting(Guid::from(Uuid::uuid4()), 12344, 1233, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('551100000000'));

        $projectPayload = [];
        $orderPayload = $this->faker->payload()->asArray();

        $data = new stdClass;
        $data->step = [];
        $data->channels = [];
        $data->destination = [];
        $data->magicLink = [];
        $data->body = [];
        $data->order = $order;
        $data->projectPayload = $projectPayload;
        $data->orderPayload = $orderPayload;

        $messageRecipient = new MessageRecipient($data);

        $user = Mockery::mock(new UserModel())
            ->makePartial()
            ->shouldReceive('xyz')
            ->andReturn(123)
            ->getMock();

        $messageParts = [new StepPart($eventType, $projectPayload), new ChannelsPart($data), new DestinationPart($data), new MagicLinkPart($data), new BodyPart($data, $user)];

        array_walk($messageParts, function (MessagePart $messagePart) use ($messageRecipient) {
            $messagePart->accept($messageRecipient);
        });
    }

    /**
     * @testWith ["OrderAccepted", "Order"]
     *           ["ProjectStarted", "Project"]
     *           ["ProjectFinished", "Project"]
     *           ["CreditCardAuthFailed", "Project"]
     *           ["CleaningComingUpNotified", "Project"]
     *           ["CleanerUpdatedTheCleaning", "Project"]
     *           ["CreditCardAuthSucceded", "Project"]
     *           ["ProjectCancelled", "Project"]
     *           ["ProjectReported", "Project"]   
     */
    public function test_given_a_project_status_then_recipient_is_created($eventType, $entityType)
    {

        $order = $this->faker->order();
        $order->fromExisting(Guid::from(Uuid::uuid4()), 1233, 1223, new OrderStatus(OrderStatus::CONFIRMED), new HumanCode(), $this->faker->payload(), new Mobile('551100000000'));

        $projectReportsPayload = $this->faker->payloadProjectReports($eventType, $entityType)->asArray();
        $orderPayload = $this->faker->payload()->asArray();

        $data = new stdClass;
        $data->step = [];
        $data->channels = [];
        $data->destination = [];
        $data->magicLink = [];
        $data->body = [];
        $data->order = $order;
        $data->projectPayload = $projectReportsPayload;
        $data->orderPayload = $orderPayload;

        $messageRecipient = Mockery::spy(new MessageRecipient($data));

        $user = Mockery::mock(new UserModel())
            ->makePartial()
            ->shouldReceive('xyz')
            ->andReturn(123)
            ->getMock();

        $messageParts = [new StepPart($eventType, $projectReportsPayload), new ChannelsPart($data), new DestinationPart($data), new MagicLinkPart($data), new BodyPart($data, $user)];

        array_walk($messageParts, function ($messagePart) use ($messageRecipient) {
            $messagePart->accept($messageRecipient);
        });

        $messageRecipient->shouldHaveReceived('visitStep');
        $messageRecipient->shouldHaveReceived('visitChannels');
        $messageRecipient->shouldHaveReceived('visitDestination');
        $messageRecipient->shouldHaveReceived('visitMagicLink');
        $messageRecipient->shouldHaveReceived('visitBody');
    }
}
