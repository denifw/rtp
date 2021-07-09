<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeItemSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_item_salary', function (Blueprint $table) {
            $table->uuid('eis_id')->primary();
            $table->uuid('eis_em_id')->unsigned();
            $table->foreign('eis_em_id', 'tbl_eis_em_id_foreign')->references('em_id')->on('employee');
            $table->uuid('eis_isl_id')->unsigned();
            $table->foreign('eis_isl_id', 'tbl_eis_isl_id_foreign')->references('isl_id')->on('item_salary');
            $table->uuid('eis_sty_id')->unsigned();
            $table->foreign('eis_sty_id', 'tbl_eis_sty_id_foreign')->references('sty_id')->on('system_type');
            $table->float('eis_amount');
            $table->uuid('eis_created_by');
            $table->dateTime('eis_created_on');
            $table->uuid('eis_updated_by')->nullable();
            $table->dateTime('eis_updated_on')->nullable();
            $table->uuid('eis_deleted_by')->nullable();
            $table->dateTime('eis_deleted_on')->nullable();
            $table->string('eis_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_item_salary');
    }
}
