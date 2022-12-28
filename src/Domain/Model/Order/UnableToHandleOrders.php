<?php

namespace Domain\Model\Order;

use Common\Exception\UnableToHandleException;

class UnableToHandleOrders extends UnableToHandleException
{

    const STATUS_TRANSITION_VIOLATION = 'Status can not be changed from %s to %s';

    const UNKNOWN_ORDER_STATE_ON_CONFIRMATION = 'Unknown order state while confirming the order %s';

    const PROJECT_NOT_FOUND = 'Project %s not found';

    const INVALID_REVIEW = 'This order can not be reviewed';
}
