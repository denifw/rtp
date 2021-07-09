<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_salary', function (Blueprint $table) {
            $table->uuid('isl_id')->primary();
            $table->uuid('isl_ss_id')->unsigned();
            $table->foreign('isl_ss_id', 'tbl_isl_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('isl_name', 256);
            $table->char('isl_active', 1)->default('Y');
            $table->uuid('isl_created_by');
            $table->dateTime('isl_created_on');
            $table->uuid('isl_updated_by')->nullable();
            $table->dateTime('isl_updated_on')->nullable();
            $table->uuid('isl_deleted_by')->nullable();
            $table->dateTime('isl_deleted_on')->nullable();
            $table->string('isl_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_salary');
    }
}
