<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\Order\OrderConfirmedEventHandler;
use Application\EventHandlers\Order\OrderCreatedEventHandler;
use Application\EventHandlers\Order\OrderUpdatedEventHandler;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\OrderEntity;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\State\OrderTransition;
use Faker\Provider\Base;
use Infrastructure\Repositories\OrderRepositoryFake;
use Infrastructure\Kafka\ProducerFake;
use Ramsey\Uuid\Uuid;
use Ddd\BnbEnqueueClient\ConnectionManager;
use Faker\Generator;

class OrderFakerProvider extends Base
{

    public function __construct(Generator $generator, private UserFakerProvider $userFakerProvider)
    {
        parent::__construct($generator);
    }

    public function invalidStatus()
    {
        return static::randomElement([1222, -12, 12, 165, 2222]);
    }

    public function payload()
    {
        return Payload::from([
            'event_type' => 'OrderConfirmed',
            'entity_type' => 'Order',
            'entity_id' => Uuid::uuid4()->toString(),
            'version' => 1,
            'event_data' =>
            [
                'cleaners' => [['id' => '2', 'name' => 'john'], ['id' => '1', 'name' => 'carlos']],
                'customer' => [
                    'payment' => ['payment_method_token' => 'ASSH16524FVJH786876jhghjghjgjh', 'customer_token' => 'ASSH16524FVJH782367863ghhjg', 'card_brand' => 'MasterCard', 'card_number' => '62726278', 'card_exp_date' => '02/32', 'card_cvc' => '123', 'card_number_last4' => '2652'],
                    'property' => ['city' => 'São Paulo', 'state' => 'SP', 'address' => 'A lks', 'zipcode' => '977297', 'extra_details' => 'ss', 'number_of_bedrooms' => 12, 'number_of_bathrooms' => 1, 'size' => 10, 'location_coordinates' => ['lat' => $this->generator->latitude(), 'long' => $this->generator->longitude()]],
                    'personal_information' => ['name' => 'ghfefegf', 'phone_number' => '5511954358134', 'email' => 'renatocostahome@gmail.com', 'country_code' => 'USA'],
                    'location' => ['timezone' => 'UTC']
                ],
                'project' => ['start_date' => 1648825200]
            ]
        ]);
    }

    public function invalidPayload()
    {
        return Payload::from([
            'event_type' => 'OrderConfirmedszzccc',
            'entity_type' => 'Order',
            'entity_id' => 222672522,
            'version' => 122
        ]);
    }

    public function order()
    {
        return \Mockery::spy(new OrderEntity(new DomainEventBus));
    }

    public function orderStatus()
    {
        return \Mockery::spy(OrderStatus::class);
    }

    public function orderTransition()
    {
        return \Mockery::mock(OrderTransition::class);
    }

    public function orderRepository(): IOrderRepository
    {
        return new OrderRepositoryFake($this);
    }

    public function confirmEventHandler($app)
    {

        $confirmEventHandler = new OrderConfirmedEventHandler($this->orderRepository());
        return $confirmEventHandler;
    }

    public function createEventHandler($app)
    {
        $createEventHandler = new OrderCreatedEventHandler($this->orderRepository());
        $createEventHandler = \Mockery::spy($createEventHandler);
        $createEventHandler->shouldReceive('handle');
        return $createEventHandler;
    }

    public function updateEventHandler($app)
    {

        $kafkaProducer = new ProducerFake(new ConnectionManager([]));

        $updateEventHandler = new OrderUpdatedEventHandler($this->orderRepository(), $kafkaProducer);
        $updateEventHandler = \Mockery::spy($updateEventHandler);
        $updateEventHandler->shouldReceive('handle');
        return $updateEventHandler;
    }
}
