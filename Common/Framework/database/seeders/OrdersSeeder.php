<?php

namespace Database\Seeders;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\Order\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Faker\Generator as Faker;

class OrdersSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        for ($i = 0; $i < 10; $i++) {
            $orderNumber = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
            DB::table('orders')->insert([
                'id' => Guid::from(Uuid::uuid4()),
                'status' => OrderStatus::CREATED,
                'order_number' => new HumanCode($orderNumber),
                'payload' => $faker->payload(),
            ]);
        }
    }
}
