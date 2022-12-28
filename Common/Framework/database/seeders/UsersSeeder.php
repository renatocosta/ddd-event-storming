<?php

namespace Database\Seeders;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Faker\Generator as Faker;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        DB::table('users')->insert([
            'id' => Guid::from(Uuid::uuid4()),
            'name' => 'John Lenon',
            'email' => $faker->email(),
            'mobile' => new HumanCode(),
            'password' => Hash::make(123456)
        ]);
    }
}
