<?php

namespace Tests\Order\Model;

use DG\BypassFinals;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Utilities\TestCase;

class OrderStatusTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_status_when_values_are_missing(): void
    {
        $this->expectException(UnableToHandleOrders::class);
        new OrderStatus($this->faker->invalidStatus());
    }

    public function test_entering_a_list_of_status_successfully(): void
    {
        foreach (OrderStatus::STATUS as $statusId => $status)
            $orderStatus = new OrderStatus($statusId);
        $this->assertEquals($orderStatus, $status);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
