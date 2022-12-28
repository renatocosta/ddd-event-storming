<?php

namespace Domain\Model\ProjectReports\MessageReport;

use Carbon\Carbon;
use stdClass;
use \Illuminate\Contracts\Auth\Authenticatable as User;

final class BodyPart extends MessagePart
{

    public function __construct(public stdClass $data, private ?User $user = null)
    {
    }

    public function body(): void
    {

        $this->data->step['parameters']['order_number'] = (string) $this->data->order->orderNumber();
        $phoneNumber = '';

        $originRequest = $this->data->step['origin_request'];

        $recipientInformation = $this->data->destination;

        if (in_array('sms', $this->data->channels) && !empty($recipientInformation['phone_number'])) {
            $this->data->step['sender']['sms']['recipient'] = $recipientInformation['phone_number'];
        }

        if (array_key_exists('cleaner_name', $this->data->step['parameters'])) {
            $this->data->step['parameters']['cleaner_name'] = $this->data->projectPayload['event_data'][$originRequest]['name'];
        }

        if (array_key_exists('user_name', $this->data->step['parameters'])) {
            $this->data->step['parameters']['user_name'] =  $originRequest == 'cleaner' ? $recipientInformation['name'] : $this->user->name;
        }

        if (array_key_exists('receiver_name', $this->data->step['parameters'])) {
            $this->data->step['parameters']['receiver_name'] = $this->data->projectPayload['event_data'][$originRequest]['name'];
        }

        if (array_key_exists('start_date', $this->data->step['parameters'])) {
            $startDate =  Carbon::createFromTimestamp($this->data->projectPayload['event_data']['project']['start_date'], $this->data->projectPayload['event_data']['customer']['location']['timezone'])->format($this->data->step['date_format']);
            $this->data->step['parameters']['start_date'] = $startDate;
        }

        if (array_key_exists('cancellation_date', $this->data->step['parameters'])) {
            $cancellationDate =  Carbon::createFromTimestamp($this->data->projectPayload['event_data']['project']['cancellation_date'], $this->data->projectPayload['event_data']['customer']['location']['timezone'])->format($this->data->step['date_format']);
            $this->data->step['parameters']['cancellation_date'] = $cancellationDate;
        }

        if (array_key_exists('report_text', $this->data->step['parameters']) && !empty($this->data->projectPayload['event_data'][$originRequest]['report_text'])) {
            $this->data->step['parameters']['report_text'] = $this->data->projectPayload['event_data'][$originRequest]['report_text'];
        }

        if (array_key_exists('magic_link', $this->data->step['parameters'])) {
            $this->data->step['parameters']['magic_link'] = $this->data->magicLink;
        }

        if (in_array('sms', $this->data->channels) && !empty($recipientInformation['phone_number'])) {
            $this->data->step['sender']['sms']['text'] = sprintf($this->data->step['sender']['sms']['text'], ...$this->extractParameters($this->data->step['sender']['sms']['text_params']));
            $phoneNumber = $recipientInformation['phone_number'];
            $this->data->step['sender']['sms']['recipient'] = $phoneNumber;
        }

        if (in_array('email', $this->data->channels)) {
            $this->data->step['sender']['email']['recipient'] = $recipientInformation['email'];
            if (array_key_exists('subject_text_params', $this->data->step['sender']['email'])) {
                $this->data->step['sender']['email']['subject'] = sprintf($this->data->step['sender']['email']['subject'], ...$this->extractParameters($this->data->step['sender']['email']['subject_text_params']));
            }
        }
    
    }

    private function extractParameters(array $paramsIncoming): array
    {
        $params = [];

        $extractFirstPart = ['cleaner_name', 'user_name'];

        foreach ($paramsIncoming as $k => $param) {

            if (in_array($param, $extractFirstPart)) {
                $this->data->step['parameters'][$param] = current(explode(' ', $this->data->step['parameters'][$param]));
            }

            $params[] = $this->data->step['parameters'][$param];
        }

        return $params;
    }

    public function accept(MessageRecipient $messageRecipient): void
    {
        $messageRecipient->visitBody($this);
    }
}
