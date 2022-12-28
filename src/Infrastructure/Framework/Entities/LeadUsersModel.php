<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadUsersModel extends Model
{

    use TraitUuid;

    protected $table = 'lead_users';

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'lead_id'];

    /**
     * Get the user associated with the order.
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }
}
