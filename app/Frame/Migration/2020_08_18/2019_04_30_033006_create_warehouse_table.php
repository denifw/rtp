<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse', function (Blueprint $table) {
            $table->bigIncrements('wh_id');
            $table->bigInteger('wh_ss_id')->nullable();
            $table->foreign('wh_ss_id', 'tbl_wh_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('wh_of_id')->nullable();
            $table->foreign('wh_of_id', 'tbl_wh_of_id_foreign')->references('of_id')->on('office');
            $table->string('wh_name', 125);
            $table->float('wh_length')->nullable();
            $table->float('wh_height')->nullable();
            $table->float('wh_width')->nullable();
            $table->float('wh_volume')->nullable();
            $table->char('wh_active', 1)->default('Y');
            $table->bigInteger('wh_created_by');
            $table->dateTime('wh_created_on');
            $table->bigInteger('wh_updated_by')->nullable();
            $table->dateTime('wh_updated_on')->nullable();
            $table->bigInteger('wh_deleted_by')->nullable();
            $table->dateTime('wh_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse');
    }
}
