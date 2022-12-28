<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{

    use TraitUuid;

    protected $table = 'orders';

    const UPDATED_AT = null;

    protected $fillable = ['id', 'user_id', 'customer_id', 'property_id', 'project_id', 'order_number', 'status', 'payload'];

    /**
     * Get the customer associated with the order.
     */
    public function customer()
    {
        return $this->hasOne(CustomersModel::class, 'id', 'customer_id');
    }

    /**
     * Get the property associated with the order.
     */
    public function property()
    {
        return $this->hasOne(PropertiesModel::class, 'id', 'property_id');
    }
}
