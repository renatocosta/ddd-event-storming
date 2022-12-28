<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Properties extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE TABLE `properties` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) unsigned NOT NULL,
            `address` varchar(200) NOT NULL,
            `zipcode` char(15) NOT NULL,
            `state` varchar(30) NOT NULL,
            `number_of_bedrooms` varchar(30) DEFAULT NULL,
            `city` varchar(100) NOT NULL,
            `extra_details` varchar(1000) DEFAULT NULL,
            `number_of_bathrooms` varchar(30) DEFAULT NULL,
            `size` varchar(30) DEFAULT NULL,
            `latitude` float(10,6) DEFAULT NULL,
            `longitude` float(10,6) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `properties_customer_id_fk` (`customer_id`),
            CONSTRAINT `properties_customer_id_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
