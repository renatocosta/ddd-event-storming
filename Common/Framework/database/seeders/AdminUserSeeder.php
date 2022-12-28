<?php

namespace Database\Seeders;

use Common\ValueObjects\Identity\Guid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Faker\Generator as Faker;
use Infrastructure\Framework\Entities\UserModel;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $userModel = new UserModel();
        $userModel->create([
            'id' => Guid::from(Uuid::uuid4()),
            'name' => 'Admin',
            'email' => env('API_USER_ADMIN'),
            'mobile' => '555555555',
            'password' => Hash::make(env('API_USER_ADMIN_PASSWORD')),
            'is_admin' => true
        ]);
    }
}
