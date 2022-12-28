<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderIntegrationEventsModel extends Model
{

    protected $table = 'order_integration_events';

    const UPDATED_AT = null;

    protected $fillable = ['type', 'destination', 'data', 'messageable_id', 'processed_at'];
}
