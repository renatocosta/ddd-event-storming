<?php

namespace Domain\Model\Notification;

use Common\Exception\UnableToHandleException;

class UnableToHandleNotification extends UnableToHandleException
{

    const MISMATCH_EVENT = 'Event %s should not be triggered';
}
