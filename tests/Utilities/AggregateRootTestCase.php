<?php

declare(strict_types=1);

namespace Tests\Utilities;

use Common\Application\Event\Eventable;
use Exception;
use Throwable;
use function assert;
use function get_class;

/**
 * @method execute($input)
 */
abstract class AggregateRootTestCase extends TestCase
{
    /**
     * @var Exception|null
     */
    private $caughtException;

    /**
     * @var object[]
     */
    private $raisedEvents = [];

    /**
     * @var string[]
     */
    private $expectedEvents = [];

    /**
     * @var Exception|null
     */
    private $theExpectedException;

    /**
     * @var bool
     */
    private $assertedScenario = false;

    /**
     * @before
     */
    protected function setUpAggregateRoot(): void
    {
        $this->raisedEvents = [];
        $this->expectedEvents = [];
        $this->assertedScenario = false;
        $this->theExpectedException = null;
        $this->caughtException = null;
    }

    /**
     * @after
     */
    protected function assertScenario(): void
    {
        // @codeCoverageIgnoreStart
        if ($this->assertedScenario) {
            return;
        }
        // @codeCoverageIgnoreEnd

        try {
            $this->assertExpectedException($this->theExpectedException, $this->caughtException);
            $this->assertRaisedEventsEqualsExpectedEvents();
        } finally {
            $this->assertedScenario = true;
            $this->theExpectedException = null;
            $this->caughtException = null;
        }
    }

    /**
     * @return $this
     */
    protected function given($input, object $useCase)
    {
        try {
            $useCase->execute($input);
        } catch (Exception $exception) {
            $this->caughtException = $exception;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function when(Eventable ...$events)
    {
        $this->raisedEvents = $events;
        return $this;
    }

    /**
     * @return $this
     */
    protected function then(string ...$events)
    {
        $this->expectedEvents = $events;

        return $this;
    }

    /**
     * @return $this
     */
    public function expectToFail(Exception $expectedException)
    {
        $this->theExpectedException = $expectedException;

        return $this;
    }

    /**
     * @return $this
     */
    protected function thenNothingShouldHaveHappened()
    {
        $this->expectedEvents = [];

        return $this;
    }

    protected function assertRaisedEventsEqualsExpectedEvents(): void
    {
        $this->expectedEvents = $this->expectedEvents;
        $this->raisedEvents = $this->extractEventNames(...$this->raisedEvents);
        self::assertEquals($this->raisedEvents, $this->expectedEvents, 'Events are not equal.');
    }

    protected function extractEventNames(Eventable ...$events): array
    {

        $eventNames = [];

        foreach ($events as $event) {
            $eventNames[] = $event->getEventName();
        }

        return $eventNames;
    }

    private function assertExpectedException(
        Exception $expectedException = null,
        Exception $caughtException = null
    ): void {
        if ($caughtException === null && $expectedException === null) {
            return;
        } elseif ($expectedException !== null && $caughtException === null) {
            throw FailedToDetectExpectedException::expectedException($expectedException);
        } elseif ($caughtException !== null && ($expectedException === null || get_class($expectedException) !== get_class(
            $caughtException
        ))) {
            throw $caughtException;
        }

        assert($expectedException instanceof Throwable);
        assert($caughtException instanceof Throwable);
        self::assertEquals(get_class($expectedException), get_class($caughtException), 'Exception types should be equal.');
    }
}
