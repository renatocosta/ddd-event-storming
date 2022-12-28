<?php

namespace Common\Application\Event;

interface Eventable
{

    /**
     * @return string
     */
    public function getEventName(): string;

    /**
     * @return string
     */
    public function trackEvent(): string;

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable;

}