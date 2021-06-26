<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkingCapitalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('working_capital', function (Blueprint $table) {
            $table->uuid('wc_id')->primary();
            $table->uuid('wc_ss_id')->unsigned();
            $table->foreign('wc_ss_id', 'tbl_wc_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('wc_ba_id')->unsigned();
            $table->foreign('wc_ba_id', 'tbl_wc_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->uuid('wc_bab_id')->unsigned();
            $table->foreign('wc_bab_id', 'tbl_wc_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->char('wc_type', 1);
            $table->date('wc_date');
            $table->time('wc_time');
            $table->dateTime('wc_transaction_on');
            $table->float('wc_amount');
            $table->string('wc_reference', 256)->nullable();
            $table->uuid('wc_created_by');
            $table->dateTime('wc_created_on');
            $table->uuid('wc_updated_by')->nullable();
            $table->dateTime('wc_updated_on')->nullable();
            $table->uuid('wc_deleted_by')->nullable();
            $table->dateTime('wc_deleted_on')->nullable();
            $table->string('wc_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('working_capital');
    }
}
