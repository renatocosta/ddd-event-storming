<?php

namespace Domain\Model\ProjectReports\MessageReport;

use Domain\Model\ProjectReports\ProjectReportsStatus;
use stdClass;

final class MagicLinkPart extends MessagePart
{

    public function __construct(public stdClass $data)
    {
    }

    public function link(): string
    {

        if (!isset($this->data->step['parameters']['magic_link'])) return '';

        if ($this->data->projectPayload['event_type'] == ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED) {
            return env('NOTIFICATIONS_MAGIC_LINK') . sprintf($this->data->step['parameters']['magic_link'], rawurlencode(base64_encode($this->data->order->mobileNumber() . '/' . $this->data->order->orderNumber())));
        }

        if ($this->data->step['origin_request'] == 'cleaner') {
            return env('NOTIFICATIONS_MAGIC_LINK') . sprintf($this->data->step['parameters']['magic_link'], rawurlencode(base64_encode($this->data->destination['phone_number'] . '/' . $this->data->order->orderNumber())));
        }

        return env('NOTIFICATIONS_MAGIC_LINK') . sprintf($this->data->step['parameters']['magic_link'], rawurlencode(base64_encode($this->data->order->getIdentifier()->id)));
    }

    public function accept(MessageRecipient $messageRecipient): void
    {
        $messageRecipient->visitMagicLink($this);
    }
}
