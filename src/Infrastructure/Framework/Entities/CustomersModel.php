<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomersModel extends Model
{

    protected $table = 'customers';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['name', 'mobile', 'email', 'country_code'];

    /**
     * Get the order associated with the customer.
     */
    public function order()
    {
        return $this->hasOne(OrderModel::class, 'customer_id');
    }
}
