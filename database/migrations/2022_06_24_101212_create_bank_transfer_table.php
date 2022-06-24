<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transfer', function (Blueprint $table) {
            $table->uuid('bt_id')->primary();
            $table->uuid('bt_ss_id')->unsigned();
            $table->foreign('bt_ss_id', 'tbl_bt_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('bt_number', 128);
            $table->uuid('bt_payer_ba_id')->unsigned();
            $table->foreign('bt_payer_ba_id', 'tbl_bt_payer_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->uuid('bt_payer_bab_id')->unsigned()->nullable();
            $table->foreign('bt_payer_bab_id', 'tbl_bt_payer_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->uuid('bt_receiver_ba_id')->unsigned();
            $table->foreign('bt_receiver_ba_id', 'tbl_bt_receiver_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->uuid('bt_receiver_bab_id')->unsigned()->nullable();
            $table->foreign('bt_receiver_bab_id', 'tbl_bt_receiver_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->date('bt_date');
            $table->time('bt_time');
            $table->dateTime('bt_datetime');
            $table->float('bt_amount');
            $table->float('bt_exchange_rate');
            $table->string('bt_notes', 255)->nullable();
            $table->uuid('bt_doc_id')->unsigned()->nullable();
            $table->foreign('bt_doc_id', 'tbl_bt_doc_id_foreign')->references('doc_id')->on('document');
            $table->dateTime('bt_paid_on')->nullable();
            $table->uuid('bt_paid_by')->unsigned()->nullable();
            $table->foreign('bt_paid_by', 'tbl_bt_paid_by_foreign')->references('us_id')->on('users');
            $table->uuid('bt_created_by');
            $table->dateTime('bt_created_on');
            $table->uuid('bt_updated_by')->nullable();
            $table->dateTime('bt_updated_on')->nullable();
            $table->uuid('bt_deleted_by')->nullable();
            $table->dateTime('bt_deleted_on')->nullable();
            $table->string('bt_deleted_reason', 256)->nullable();
            $table->unique(['bt_ss_id', 'bt_number'], 'tb_bt_ss_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_transfer');
    }
}
