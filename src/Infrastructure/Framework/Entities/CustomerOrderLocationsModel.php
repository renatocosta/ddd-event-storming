<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderLocationsModel extends Model
{

    protected $table = 'customer_order_locations';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['timezone', 'order_id', 'customer_id'];
}
