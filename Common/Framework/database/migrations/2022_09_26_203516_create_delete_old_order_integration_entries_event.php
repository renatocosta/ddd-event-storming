<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDeleteOldOrderIntegrationEntriesEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("CREATE EVENT delete_old_order_integration_entries
                        ON SCHEDULE EVERY 1 DAY ON COMPLETION PRESERVE
                        DO 
                         DELETE FROM order_integration_events WHERE processed_at IS NOT NULL AND processed_at > now() - INTERVAL 7 day;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP EVENT delete_old_order_integration_entries;');
    }
}
