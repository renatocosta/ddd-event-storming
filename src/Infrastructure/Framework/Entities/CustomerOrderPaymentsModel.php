<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderPaymentsModel extends Model
{

    protected $table = 'customer_order_payments';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['customer_id', 'order_id', 'payment_method_token', 'card_number_last4', 'customer_token'];
}
