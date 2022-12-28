<?php

namespace Infrastructure\Repositories;

use Infrastructure\Framework\Entities\AffiliateCustomerConversionsModel;

final class AffiliateCustomerConversionsRepository
{

    public function create(string $userId, string $orderId, string $stripeCustomerId, string $linkminkConversionId)
    {
        (new AffiliateCustomerConversionsModel())->on('mysql::write')->create(
            ['user_id' => $userId, 'order_id' => $orderId, 'stripe_customer_id' => $stripeCustomerId, 'linkmink_conversion_id' => $linkminkConversionId]
        );
    }
}
