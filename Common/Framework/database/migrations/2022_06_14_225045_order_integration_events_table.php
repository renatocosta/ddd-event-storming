<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderIntegrationEventsTable extends Migration
{

    public function up()
    {
        Schema::create('order_integration_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 100);
            $table->char('destination', 50);
            $table->json('data');
            $table->char('messageable_id', 36);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_integration_events');
    }
}
