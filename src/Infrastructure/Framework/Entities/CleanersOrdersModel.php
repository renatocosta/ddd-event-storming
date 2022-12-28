<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class CleanersOrdersModel extends Model
{

    protected $table = 'cleaners_orders';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['cleaner_id', 'order_id'];
}
