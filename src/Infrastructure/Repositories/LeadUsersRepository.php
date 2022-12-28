<?php

namespace Infrastructure\Repositories;

use Infrastructure\Framework\Entities\LeadUsersModel;

class LeadUsersRepository
{

    public function get(string $userId)
    {
        return (new LeadUsersModel())->on('mysql::read')->select('lead_id')->where('user_id', $userId)->first();
    }

    public function create(string $userId, string $leadId)
    {
        (new LeadUsersModel())->on('mysql::write')->create(
            ['user_id' => $userId, 'lead_id' => $leadId]
        );
    }
}
