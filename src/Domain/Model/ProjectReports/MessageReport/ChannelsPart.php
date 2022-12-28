<?php

namespace Domain\Model\ProjectReports\MessageReport;

use Domain\Model\ProjectReports\ProjectReportsStatus;
use stdClass;

final class ChannelsPart extends MessagePart
{

    private array $channels = [];

    public function __construct(public stdClass $data)
    {
    }

    public function determineChannels(): void
    {
        if ($this->data->projectPayload['event_type'] == ProjectReportsStatus::PROJECT_REPORTED) {
            $this->channels  = [$this->data->projectPayload['event_data']['request']['channel']];
            return;
        }

        $this->channels = array_keys($this->data->step['sender']);
    }

    public function selection(): array
    {
        return $this->channels;
    }

    public function accept(MessageRecipient $messageRecipient): void
    {
        $messageRecipient->visitChannels($this);
    }
}
