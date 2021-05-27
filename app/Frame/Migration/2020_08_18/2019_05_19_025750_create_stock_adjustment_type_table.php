<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockAdjustmentTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjustment_type', function (Blueprint $table) {
            $table->bigIncrements('sat_id');
            $table->bigInteger('sat_ss_id')->unsigned();
            $table->foreign('sat_ss_id', 'tbl_sat_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('sat_code', 125);
            $table->string('sat_description', 255);
            $table->char('sat_active', 1)->default('Y');
            $table->bigInteger('sat_created_by');
            $table->dateTime('sat_created_on');
            $table->bigInteger('sat_updated_by')->nullable();
            $table->dateTime('sat_updated_on')->nullable();
            $table->bigInteger('sat_deleted_by')->nullable();
            $table->dateTime('sat_deleted_on')->nullable();
            $table->unique(['sat_ss_id', 'sat_code'], 'tbl_sat_ss_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_adjustment_type');
    }
}
