<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateCustomerConversionsModel extends Model
{

    use TraitUuid;

    protected $table = 'affiliate_customer_conversions';

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'order_id', 'stripe_customer_id', 'linkmink_conversion_id'];

    public function user()
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }

    public function order()
    {
        return $this->hasOne(OrderModel::class, 'id', 'order_id');
    }

}
