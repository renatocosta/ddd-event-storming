<?php

namespace Database\Seeders;

use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\ProjectReports\ProjectStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;

class ProjectReportsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        DB::table('project_status_reports')->insert([
            'order_id' => '03e6368d-cd27-4d68-9ae4-5ab1f863d801',
            'mobile' => new Mobile('5511954321234'),
            'status' => ProjectStatus::STATUS['OrderAccepted'],
            'order_number' => new HumanCode(),
            'payload' => $faker->payloadProject(),
        ]);
    }
}
