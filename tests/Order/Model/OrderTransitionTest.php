<?php

namespace Tests\Order\Model;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Payload;
use DG\BypassFinals;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\State\OrderTransition;
use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\Utilities\TestCase;

class OrderTransitionTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_state(): void
    {
        $this->expectException(UnableToHandleOrders::class);
        $orderStatus = $this->faker->orderStatus();
        $orderStatus->shouldReceive('getId')
            ->andReturn($invalidStatusId = 6245654);
        $order = $this->faker->order();
        $order->of(Guid::from(Uuid::uuid4()), 1222, 12334, new HumanCode(), new Payload('{}'));
        $order->shouldReceive('getStatus')
            ->andReturn($orderStatus);
        new OrderTransition($order);
    }

    public function test_entering_a_valid_status(): void
    {
        foreach (OrderStatus::STATUS as $statusId => $status)
            $order = $this->faker->order();
        $order->shouldReceive('getStatus')
            ->andReturn(new OrderStatus($statusId));
        $orderTransition = new OrderTransition($order);
        $this->assertNotEmpty($orderTransition);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
