<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class CleanersModel extends Model
{

    protected $table = 'cleaners';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['name', 'referenced_id'];
}
