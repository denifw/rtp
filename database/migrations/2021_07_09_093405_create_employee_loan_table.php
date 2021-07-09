<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_loan', function (Blueprint $table) {
            $table->uuid('el_id')->primary();
            $table->uuid('el_ss_id')->unsigned();
            $table->foreign('el_ss_id', 'tbl_el_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->uuid('el_em_id')->unsigned();
            $table->foreign('el_em_id', 'tbl_el_em_id_foreign')->references('em_id')->on('employee');
            $table->char('el_type', 1);
            $table->string('el_number', 256);
            $table->float('el_amount');
            $table->string('el_notes', 256)->nullable();

            $table->uuid('el_approve_by')->unsigned()->nullable();
            $table->foreign('el_approve_by', 'tbl_el_approve_by_foreign')->references('us_id')->on('users');
            $table->dateTime('el_approve_on')->nullable();

            $table->date('el_pay_date')->nullable();
            $table->uuid('el_paid_by')->unsigned()->nullable();
            $table->foreign('el_paid_by', 'tbl_el_paid_by_foreign')->references('us_id')->on('users');
            $table->dateTime('el_paid_on')->nullable();
            $table->uuid('el_ba_id')->unsigned()->nullable();
            $table->foreign('el_ba_id', 'tbl_el_ba_id_foreign')->references('ba_id')->on('bank_account');
            $table->uuid('el_bab_id')->unsigned()->nullable();
            $table->foreign('el_bab_id', 'tbl_el_bab_id_foreign')->references('bab_id')->on('bank_account_balance');
            $table->uuid('el_elb_id')->unsigned()->nullable();
            $table->foreign('el_elb_id', 'tbl_el_elb_id_foreign')->references('elb_id')->on('employee_loan_balance');

            $table->uuid('el_created_by');
            $table->dateTime('el_created_on');
            $table->uuid('el_updated_by')->nullable();
            $table->dateTime('el_updated_on')->nullable();
            $table->uuid('el_deleted_by')->nullable();
            $table->dateTime('el_deleted_on')->nullable();
            $table->string('el_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_loan');
    }
}
