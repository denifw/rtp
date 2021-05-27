<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreatePettyCashTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('petty_cash_request');
        Schema::dropIfExists('petty_cash');
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->bigIncrements('ptc_id');
            $table->string('ptc_number', 125);
            $table->bigInteger('ptc_ss_id')->unsigned();
            $table->foreign('ptc_ss_id', 'tbl_ptc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('ptc_cac_id')->unsigned();
            $table->foreign('ptc_cac_id', 'tbl_ptc_cac_id_foreign')->references('cac_id')->on('cash_account');
            $table->date('ptc_date');
            $table->float('ptc_amount');
            $table->string('ptc_notes', 255)->nullable();
            $table->bigInteger('ptc_approve_by')->nullable();
            $table->dateTime('ptc_approve_on')->nullable();
            $table->bigInteger('ptc_paid_by')->nullable();
            $table->string('ptc_paid_ref', 255)->nullable();
            $table->dateTime('ptc_paid_on')->nullable();
            $table->string('ptc_receipt', 255)->nullable();
            $table->bigInteger('ptc_cb_id')->unsigned()->nullable();
            $table->foreign('ptc_cb_id', 'tbl_ptc_cb_id_foreign')->references('cb_id')->on('cash_balance');
            $table->bigInteger('ptc_created_by');
            $table->dateTime('ptc_created_on');
            $table->bigInteger('ptc_updated_by')->nullable();
            $table->dateTime('ptc_updated_on')->nullable();
            $table->string('ptc_deleted_reason', 255)->nullable();
            $table->bigInteger('ptc_deleted_by')->nullable();
            $table->dateTime('ptc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petty_cash');
    }
}
