<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice', function (Blueprint $table) {
            $table->uuid('pi_id')->primary();
            $table->uuid('pi_ss_id')->unsigned();
            $table->foreign('pi_ss_id', 'tbl_pi_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('pi_number', 128);
            $table->string('pi_reference', 256)->nullable();
            $table->uuid('pi_rel_id')->unsigned()->nullable();
            $table->foreign('pi_rel_id', 'tbl_pi_rel_id_foreign')->references('rel_id')->on('relation');
            $table->uuid('pi_cp_id')->unsigned()->nullable();
            $table->foreign('pi_cp_id', 'tbl_pi_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->date('pi_date');
            $table->date('pi_due_date')->nullable();
            $table->string('pi_notes', 256)->nullable();
            $table->uuid('pi_ba_id')->unsigned()->nullable();
            $table->foreign('pi_ba_id', 'tbl_pi_ba_id_foreign')->references('ba_id')->on('bank_account');
            $table->uuid('pi_bab_id')->unsigned()->nullable();
            $table->foreign('pi_bab_id', 'tbl_pi_bab_id_foreign')->references('bab_id')->on('bank_account_balance');
            $table->date('pi_pay_date')->nullable();
            $table->dateTime('pi_paid_on')->nullable();
            $table->uuid('pi_paid_by')->unsigned()->nullable();
            $table->foreign('pi_paid_by', 'tbl_pi_paid_by_foreign')->references('us_id')->on('users');
            $table->dateTime('pi_verified_on')->nullable();
            $table->uuid('pi_verified_by')->unsigned()->nullable();
            $table->foreign('pi_verified_by', 'tbl_pi_verified_by_foreign')->references('us_id')->on('users');
            $table->uuid('pi_created_by');
            $table->dateTime('pi_created_on');
            $table->uuid('pi_updated_by')->nullable();
            $table->dateTime('pi_updated_on')->nullable();
            $table->uuid('pi_deleted_by')->nullable();
            $table->dateTime('pi_deleted_on')->nullable();
            $table->string('pi_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice');
    }
}
