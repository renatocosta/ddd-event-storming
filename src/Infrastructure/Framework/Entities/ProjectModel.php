<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class ProjectModel extends Model
{

    protected $table = 'project';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $fillable = ['start_date', 'end_date', 'preferred_time_period', 'preferred_time_start_date', 'preferred_time_end_date', 'referenced_id'];
}
