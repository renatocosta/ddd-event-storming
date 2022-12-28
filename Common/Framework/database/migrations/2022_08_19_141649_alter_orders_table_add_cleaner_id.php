<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterOrdersTableAddCleanerId extends Migration
{
    public function up()
    {

        DB::statement('SET SESSION sql_require_primary_key=0');

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('cleaner_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cleaner_id');
        });
    }
}
