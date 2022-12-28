<?php

namespace Domain\Model\ProjectReports\MessageReport;

interface MessageRecipientable
{

    public function visitStep(StepPart $stepPart): void;

    public function visitChannels(ChannelsPart $channelsPart): void;

    public function visitDestination(DestinationPart $destinationPart): void;

    public function visitMagicLink(MagicLinkPart $magicLinkPart): void;

    public function visitBody(BodyPart $bodyPart): void;

    public function result(): array;
}
