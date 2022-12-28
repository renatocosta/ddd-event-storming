<?php

namespace Interfaces\Incoming\Stream;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Mixpanel;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;

class TrackOrderToBusinessAnalyticsConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {

        $mixPanel = app(Mixpanel::class);

        $data = json_decode($message->getBody(), true);
        $mixPanel->identify($data['user_id']);
        $mixPanel->track($data['event_type'], $data['data']);
        $mixPanel->flush();
    }
}
