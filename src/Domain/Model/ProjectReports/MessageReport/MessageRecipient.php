<?php

namespace Domain\Model\ProjectReports\MessageReport;

use stdClass;

final class MessageRecipient implements MessageRecipientable
{

    private array $output = [];

    private DestinationPart $destinationPart;

    public function __construct(public stdClass $data)
    {
    }

    public function visitStep(StepPart $stepPart): void
    {
        $this->data->step = $stepPart->selection();
    }

    public function visitChannels(ChannelsPart $channelsPart): void
    {
        $channelsPart->determineChannels();
        $this->data->channels = $channelsPart->selection();
    }

    public function visitDestination(DestinationPart $destinationPart): void
    {
        $this->destinationPart = $destinationPart;
        $this->data->destination = $destinationPart->info();
    }

    public function visitMagicLink(MagicLinkPart $magicLinkPart): void
    {
        $this->data->magicLink = $magicLinkPart->link();
    }

    public function visitBody(BodyPart $bodyPart): void
    {
        $bodyPart->body();
        $this->output = $this->data->step;
    }

    public function result(): array
    {
        return $this->output;
    }
}
