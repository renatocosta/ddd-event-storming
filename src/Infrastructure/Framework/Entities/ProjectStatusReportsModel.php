<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusReportsModel extends Model
{

    protected $table = 'project_status_reports';

    const UPDATED_AT = null;

    protected $fillable = ['order_id', 'mobile', 'order_number', 'status', 'payload'];
}
