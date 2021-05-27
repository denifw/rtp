<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transaction', function (Blueprint $table) {
            $table->bigIncrements('bt_id');
            $table->bigInteger('bt_ss_id')->unsigned();
            $table->foreign('bt_ss_id', 'tbl_bt_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('bt_number', 128);
            $table->string('bt_type', 64);
            $table->bigInteger('bt_payer_ba_id')->unsigned()->nullable();
            $table->foreign('bt_payer_ba_id', 'tbl_bt_payer_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->bigInteger('bt_payer_bab_id')->unsigned()->nullable();
            $table->foreign('bt_payer_bab_id', 'tbl_bt_payer_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->bigInteger('bt_receiver_ba_id')->unsigned()->nullable();
            $table->foreign('bt_receiver_ba_id', 'tbl_bt_receiver_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->bigInteger('bt_receiver_bab_id')->unsigned()->nullable();
            $table->foreign('bt_receiver_bab_id', 'tbl_bt_receiver_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->float('bt_amount');
            $table->float('bt_currency_exchange');
            $table->string('bt_notes', 255)->nullable();
            $table->bigInteger('bt_approve_by')->nullable();
            $table->foreign('bt_approve_by', 'tbl_bt_approve_by_foreign')->references('us_id')->on('users');
            $table->dateTime('bt_approve_on')->nullable();
            $table->bigInteger('bt_paid_by')->nullable();
            $table->foreign('bt_paid_by', 'tbl_bt_paid_by_foreign')->references('us_id')->on('users');
            $table->string('bt_paid_ref', 255)->nullable();
            $table->dateTime('bt_paid_on')->nullable();
            $table->bigInteger('bt_doc_id')->unsigned()->nullable();
            $table->foreign('bt_doc_id', 'tbl_bt_doc_id_foreign')->references('doc_id')->on('document');
            $table->bigInteger('bt_receive_by')->nullable();
            $table->foreign('bt_receive_by', 'tbl_bt_receive_by_foreign')->references('us_id')->on('users');
            $table->dateTime('bt_receive_on')->nullable();
            $table->bigInteger('bt_synchronize_by')->nullable();
            $table->foreign('bt_synchronize_by', 'tbl_bt_synchronize_by_foreign')->references('us_id')->on('users');
            $table->dateTime('bt_synchronize_on')->nullable();
            $table->bigInteger('bt_created_by');
            $table->dateTime('bt_created_on');
            $table->bigInteger('bt_updated_by')->nullable();
            $table->dateTime('bt_updated_on')->nullable();
            $table->bigInteger('bt_deleted_by')->nullable();
            $table->dateTime('bt_deleted_on')->nullable();
            $table->string('bt_deleted_reason', 256)->nullable();
            $table->uuid('bt_uid');
            $table->unique('bt_uid', 'tbl_bt_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_transaction');
    }
}
