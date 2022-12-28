<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanersOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("CREATE TABLE `cleaners_orders` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `cleaner_id` int(11) unsigned NOT NULL,
    `order_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cleaners_orders_UN` (`order_id`,`cleaner_id`),
    CONSTRAINT `cleaners_orders_cleaner_id_FK` FOREIGN KEY (`cleaner_id`) REFERENCES `cleaners` (`id`),
    CONSTRAINT `cleaners_orders_order_id_FK` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
  )      ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cleaners_orders');
    }
}
