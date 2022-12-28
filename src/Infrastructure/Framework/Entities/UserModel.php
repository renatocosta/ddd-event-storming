<?php

namespace Infrastructure\Framework\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, TraitUuid, SoftDeletes;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'mobile',
        'password',
        'customer_id',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the customer associated with the order.
     */
    public function customer()
    {
        return $this->hasOne(CustomersModel::class, 'id', 'customer_id');
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
}
