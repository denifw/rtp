<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeLoanRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_loan_request', function (Blueprint $table) {
            $table->uuid('elr_id')->primary();
            $table->uuid('elr_el_id')->unsigned();
            $table->foreign('elr_el_id', 'tbl_elr_el_id_foreign')->references('el_id')->on('employee_loan');
            $table->uuid('elr_created_by');
            $table->dateTime('elr_created_on');
            $table->uuid('elr_updated_by')->nullable();
            $table->dateTime('elr_updated_on')->nullable();
            $table->uuid('elr_deleted_by')->nullable();
            $table->dateTime('elr_deleted_on')->nullable();
            $table->string('elr_deleted_reason', 256)->nullable();
        });
        Schema::table('employee_loan', function (Blueprint $table) {
            $table->uuid('el_elr_id')->unsigned()->nullable();
            $table->foreign('el_elr_id', 'tbl_el_elr_id_foreign')->references('elr_id')->on('employee_loan_request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_loan', function (Blueprint $table) {
            $table->dropForeign('tbl_el_elr_id_foreign');
            $table->dropColumn('el_elr_id');
        });
        Schema::dropIfExists('employee_loan_request');
    }
}
