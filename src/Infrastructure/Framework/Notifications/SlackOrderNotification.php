<?php

namespace Infrastructure\Framework\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackOrderNotification extends Notification
{

    private array $parameters = [];

    public function __construct(private SlackMessage $slackMessage)
    {
    }

    public function setParameters(array $parameters = []): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return $this->slackMessage
            ->from('DDD Order', ':dddcom:')
            ->info()
            ->content($this->parameters['message'])
            ->attachment(function ($attachment) {
                $attachment->field(function ($field) {
                    $field->title('Customer')->content($this->parameters['customer']);
                });
                $attachment->field(function ($field) {
                    $field->title('Location')->content($this->parameters['location']);
                });
                $attachment->field(function ($field) {
                    $field->title($this->parameters['followup_date_title'])->content($this->parameters['followup_date']);
                });
                $attachment->field(function ($field) {
                    $field->title('Time')->content($this->parameters['time']);
                });
                $attachment->field(function ($field) {
                    $field->title('DDD Order ID')->content($this->parameters['orderId'])->long();
                });
            });
    }
}
