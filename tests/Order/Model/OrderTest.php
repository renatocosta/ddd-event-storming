<?php

namespace Tests\Order\Model;

use DG\BypassFinals;
use Domain\Model\Order\Order;
use Domain\Model\Order\OrderStatus;
use Domain\Model\Order\State\OrderTransition;
use Domain\Model\Order\UnableToHandleOrders;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Utilities\TestCase;

class OrderTest extends TestCase
{

    private Order $order;

    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->order = $this->faker->order();
    }

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_create_order_with_invalid_transitions_should_be_considered_invalid(): void
    {
        $transitions = $this->invalidTransitions(OrderStatus::UNCREATED, 'create');
        $this->assertEquals($transitions['numberOfExpectedFailures'], $transitions['totalInvalidTransitions']);
    }

    public function test_confirm_order_with_invalid_transitions_should_be_considered_invalid(): void
    {
        $transitions = $this->invalidTransitions(OrderStatus::UNCREATED, 'uncreate');
        $this->assertEquals($transitions['numberOfExpectedFailures'], $transitions['totalInvalidTransitions']);
    }

    public function test_uncreate_order_with_invalid_transitions_should_be_considered_invalid(): void
    {
        $transitions = $this->invalidTransitions(OrderStatus::CREATED, 'confirm');
        $this->assertEquals($transitions['numberOfExpectedFailures'], $transitions['totalInvalidTransitions']);
    }

    public function test_update_order_with_invalid_transitions_should_be_considered_invalid(): void
    {
        $transitions = $this->invalidTransitions(OrderStatus::CONFIRMED, 'update');
        $this->assertEquals($transitions['numberOfExpectedFailures'], $transitions['totalInvalidTransitions']);
    }

    public function test_entering_a_list_of_transitions_successfully(): void
    {
        try {
            $order = $this->validTransitions(OrderStatus::UNCREATED);
            $order->create();
            $order = $this->validTransitions(OrderStatus::CREATED);
            $order->confirm();
            $order = $this->validTransitions(OrderStatus::CONFIRMED);
            $order->update();
            $order = $this->validTransitions(OrderStatus::UPDATED);
        } catch (Exception $e) {
            $this->fail();
        }
        self::expectNotToPerformAssertions();
    }

    public function validTransitions(int $orderStatus): Order
    {
        $order = $this->faker->order();
        $order->shouldReceive('getStatus')->andReturn(new OrderStatus($orderStatus));
        $orderTransition = new OrderTransition($order);
        return $order
            ->fromState($orderTransition->nextState());
    }

    public function invalidTransitions(int $orderStatus, string $transition): array
    {
        $this->order->shouldReceive('getStatus')
            ->andReturn(new OrderStatus($orderStatus));
        $orderTransition = new OrderTransition($this->order);
        $this->order
            ->fromState($orderTransition->nextState());
        $invalidNextTransitions = $this->invalidNextTransitions()[$transition];
        $numberOfExpectedFailures = count($invalidNextTransitions);

        $totalInvalidTransitions = array_reduce($invalidNextTransitions, function ($totalFound = 0, $transition = null) {
            try {
                $this->order->$transition();
            } catch (UnableToHandleOrders $e) {
                $totalFound++;
            }
            return $totalFound;
        });

        return ['numberOfExpectedFailures' => $numberOfExpectedFailures, 'totalInvalidTransitions' => $totalInvalidTransitions];
    }

    public function invalidNextTransitions(): array
    {
        return [
            'confirm'    => ['uncreate', 'create'],
            'create' => ['uncreate', 'confirm', 'update'],
            'uncreate' => ['uncreate', 'confirm', 'update'],
            'update' => ['uncreate', 'create', 'confirm']
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
