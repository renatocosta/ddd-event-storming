<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectStatusReportsTableAddMobileColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_status_reports', function (Blueprint $table) {
            $table->after('order_id', function ($table) {
                $table->string('mobile');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_status_reports', function (Blueprint $table) {
            $table->dropColumn('mobile');
        });
    }
}
