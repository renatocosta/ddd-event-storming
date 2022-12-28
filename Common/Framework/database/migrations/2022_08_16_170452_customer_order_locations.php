<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerOrderLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `customer_order_locations` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `timezone` varchar(50) NOT NULL,
            `order_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `customer_id` int(11) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `customer_order_locations_UN` (`order_id`,`customer_id`),
            KEY `customer_order_locations_customer_id` (`customer_id`),
            CONSTRAINT `customer_order_locations_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
            CONSTRAINT `customer_order_locations_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_order_locations');
    }
}
