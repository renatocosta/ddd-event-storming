<?php

namespace Interfaces\Incoming\Stream;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;
use Illuminate\Support\Facades\Notification;
use Infrastructure\Framework\Notifications\SlackOrderNotification;

class PostMessagesToSlackChannelConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {

        $data = json_decode($message->getBody(), true);

        $slackOrderNotification = app(SlackOrderNotification::class);
        $slackOrderNotification->setParameters($data);
        Notification::route('slack', env('SLACK_ORDER_WEBHOOK_URL'))
            ->notify($slackOrderNotification);
    }
}
