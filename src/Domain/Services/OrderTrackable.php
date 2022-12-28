<?php

namespace Domain\Services;

use Common\ValueObjects\Identity\Identified;
use Domain\Model\Order\Order;

interface OrderTrackable
{
    public function fetchCurrent(Identified $orderId): Order;
}
