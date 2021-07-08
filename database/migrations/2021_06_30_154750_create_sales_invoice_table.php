<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice', function (Blueprint $table) {
            $table->uuid('si_id')->primary();
            $table->uuid('si_ss_id')->unsigned();
            $table->foreign('si_ss_id', 'tbl_si_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('si_number', 128)->nullable();
            $table->uuid('si_rel_id')->unsigned();
            $table->foreign('si_rel_id', 'tbl_si_rel_id_foreign')->references('rel_id')->on('relation');
            $table->uuid('si_of_id')->unsigned();
            $table->foreign('si_of_id', 'tbl_si_of_id_foreign')->references('of_id')->on('office');
            $table->uuid('si_cp_id')->unsigned();
            $table->foreign('si_cp_id', 'tbl_si_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->uuid('si_jo_id')->unsigned()->nullable();
            $table->foreign('si_jo_id', 'tbl_si_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->uuid('si_pt_id')->unsigned();
            $table->foreign('si_pt_id', 'tbl_si_pt_id_foreign')->references('pt_id')->on('payment_terms');
            $table->uuid('si_pm_id')->unsigned()->nullable();
            $table->foreign('si_pm_id', 'tbl_si_pm_id_foreign')->references('pm_id')->on('payment_method');
            $table->uuid('si_ba_id')->unsigned();
            $table->foreign('si_ba_id', 'tbl_si_ba_id_foreign')->references('ba_id')->on('bank_account');
            $table->uuid('si_bab_id')->unsigned()->nullable();
            $table->foreign('si_bab_id', 'tbl_si_bab_id_foreign')->references('bab_id')->on('bank_account_balance');
            $table->date('si_date')->nullable();
            $table->dateTime('si_submit_on')->nullable();
            $table->uuid('si_submit_by')->unsigned()->nullable();
            $table->foreign('si_submit_by', 'tbl_si_submit_by_foreign')->references('us_id')->on('users');
            $table->date('si_due_date')->nullable();
            $table->date('si_pay_date')->nullable();
            $table->dateTime('si_paid_on')->nullable();
            $table->uuid('si_paid_by')->unsigned()->nullable();
            $table->foreign('si_paid_by', 'tbl_si_paid_by_foreign')->references('us_id')->on('users');
            $table->uuid('si_created_by');
            $table->dateTime('si_created_on');
            $table->uuid('si_updated_by')->nullable();
            $table->dateTime('si_updated_on')->nullable();
            $table->uuid('si_deleted_by')->nullable();
            $table->dateTime('si_deleted_on')->nullable();
            $table->string('si_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice');
    }
}
