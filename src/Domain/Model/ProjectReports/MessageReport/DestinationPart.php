<?php

namespace Domain\Model\ProjectReports\MessageReport;

use stdClass;

final class DestinationPart extends MessagePart
{

    public function __construct(public stdClass $data)
    {
    }

    public function info(): array
    {
        if ($this->data->step['origin_request'] == 'cleaner') {
            $payloadCustomer = $this->data->orderPayload;
            return $payloadCustomer['event_data']['customer']['personal_information'];
        }

        $originData = $this->data->projectPayload['event_data']['request'];

        return $originData['channel'] == 'email' ? ['email' => $originData['email']] : ['phone_number' => $originData['phone_number']];
    }

    public function accept(MessageRecipient $messageRecipient): void
    {
        $messageRecipient->visitDestination($this);
    }
}
