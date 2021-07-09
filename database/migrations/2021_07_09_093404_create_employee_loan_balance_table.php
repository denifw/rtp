<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeLoanBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_loan_balance', function (Blueprint $table) {
            $table->uuid('elb_id')->primary();
            $table->uuid('elb_em_id')->unsigned();
            $table->foreign('elb_em_id', 'tbl_elb_em_id_foreign')->references('em_id')->on('employee');
            $table->float('elb_amount');
            $table->uuid('elb_created_by');
            $table->dateTime('elb_created_on');
            $table->uuid('elb_updated_by')->nullable();
            $table->dateTime('elb_updated_on')->nullable();
            $table->uuid('elb_deleted_by')->nullable();
            $table->dateTime('elb_deleted_on')->nullable();
            $table->string('elb_deleted_reason', 256)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_loan_balance');
    }
}
