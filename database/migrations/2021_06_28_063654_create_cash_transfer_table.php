<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_transfer', function (Blueprint $table) {
            $table->uuid('ct_id')->primary();
            $table->uuid('ct_ss_id')->unsigned();
            $table->foreign('ct_ss_id', 'tbl_ct_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('ct_number', 128);
            $table->uuid('ct_payer_ba_id')->unsigned()->nullable();
            $table->foreign('ct_payer_ba_id', 'tbl_ct_payer_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->uuid('ct_payer_bab_id')->unsigned()->nullable();
            $table->foreign('ct_payer_bab_id', 'tbl_ct_payer_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->uuid('ct_receiver_ba_id')->unsigned()->nullable();
            $table->foreign('ct_receiver_ba_id', 'tbl_ct_receiver_ba_id_fkey')->references('ba_id')->on('bank_account');
            $table->uuid('ct_receiver_bab_id')->unsigned()->nullable();
            $table->foreign('ct_receiver_bab_id', 'tbl_ct_receiver_bab_id_fkey')->references('bab_id')->on('bank_account_balance');
            $table->date('ct_date');
            $table->float('ct_amount');
            $table->float('ct_currency_exchange');
            $table->string('ct_notes', 255)->nullable();
            $table->uuid('ct_doc_id')->unsigned()->nullable();
            $table->foreign('ct_doc_id', 'tbl_ct_doc_id_foreign')->references('doc_id')->on('document');
            $table->uuid('ct_created_by');
            $table->dateTime('ct_created_on');
            $table->uuid('ct_updated_by')->nullable();
            $table->dateTime('ct_updated_on')->nullable();
            $table->uuid('ct_deleted_by')->nullable();
            $table->dateTime('ct_deleted_on')->nullable();
            $table->string('ct_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_transfer');
    }
}
