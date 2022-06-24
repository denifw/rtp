<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateOnUpdateBabTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE OR REPLACE FUNCTION do_update_ba_balance_on_update_bab()
                                RETURNS TRIGGER
                                LANGUAGE PLPGSQL
                            AS $$
                            BEGIN
                                UPDATE bank_account
                                SET ba_current_balance = b.total
                                FROM (SELECT bab_ba_id, SUM(bab_amount) as total
                                        FROM bank_account_balance
                                        WHERE bab_deleted_on IS NULL and bab_ba_id = old.bab_ba_id
                                        GROUP BY bab_ba_id) as b
                                WHERE ba_id = b.bab_ba_id and ba_id = old.bab_ba_id;
                                RETURN new;
                            END;
                            $$');
        DB::unprepared('CREATE TRIGGER bab_on_update_trigger
                              AFTER UPDATE
                              ON bank_account_balance
                              FOR EACH ROW
                              EXECUTE PROCEDURE do_update_ba_balance_on_update_bab()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER bab_on_update_trigger ON bank_account_balance');
        DB::unprepared('DROP FUNCTION do_update_ba_balance_on_update_bab');
    }
}
