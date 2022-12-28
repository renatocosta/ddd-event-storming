<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertiesModel extends Model
{

    protected $table = 'properties';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['customer_id', 'address', 'zipcode', 'state', 'number_of_bedrooms', 'city', 'extra_details', 'number_of_bathrooms', 'size', 'latitude', 'longitude'];
}
