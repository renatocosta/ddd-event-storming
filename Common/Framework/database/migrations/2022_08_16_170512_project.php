<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Project extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `project` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `start_date` timestamp NOT NULL,
            `end_date` timestamp NULL,
            `preferred_time_period` varchar(20) DEFAULT NULL,
            `preferred_time_start_date` timestamp NULL DEFAULT NULL,
            `preferred_time_end_date` timestamp NULL DEFAULT NULL,
            `referenced_id` int(11) DEFAULT NULL,
            UNIQUE KEY `project_UN` (`referenced_id`),
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project');
    }
}
