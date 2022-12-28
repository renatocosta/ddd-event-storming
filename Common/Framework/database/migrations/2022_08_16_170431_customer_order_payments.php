<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerOrderPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `customer_order_payments` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) unsigned NOT NULL,
            `order_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `payment_method_token` text NOT NULL,
            `card_number_last4` char(4) DEFAULT NULL,
            `customer_token` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `customer_order_payments_UN` (`order_id`,`customer_id`),
            KEY `customer_order_payments_customer_id` (`customer_id`),
            CONSTRAINT `customer_order_payments_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
            CONSTRAINT `customer_order_payments_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_order_payments');
    }
}
