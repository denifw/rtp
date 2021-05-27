<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouteDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_delivery', function (Blueprint $table) {
            $table->bigIncrements('rd_id');
            $table->bigInteger('rd_ss_id')->unsigned();
            $table->foreign('rd_ss_id','tbl_rd_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('rd_code',125);
            $table->bigInteger('rd_dtc_or_id')->unsigned();
            $table->foreign('rd_dtc_or_id','tbl_rd_dtc_or_id_foreign')->references('dtc_id')->on('district');
            $table->bigInteger('rd_dtc_des_id')->unsigned();
            $table->foreign('rd_dtc_des_id','tbl_rd_dtc_des_id_foreign')->references('dtc_id')->on('district');
            $table->float('rd_distance');
            $table->float('rd_drive_time');
            $table->float('rd_toll_1')->nullable();
            $table->float('rd_toll_2')->nullable();
            $table->float('rd_toll_3')->nullable();
            $table->float('rd_toll_4')->nullable();
            $table->float('rd_toll_5')->nullable();
            $table->float('rd_toll_6')->nullable();
            $table->char('rd_active', 1)->default('Y');
            $table->bigInteger('rd_created_by');
            $table->dateTime('rd_created_on');
            $table->bigInteger('rd_updated_by')->nullable();
            $table->dateTime('rd_updated_on')->nullable();
            $table->bigInteger('rd_deleted_by')->nullable();
            $table->dateTime('rd_deleted_on')->nullable();
            $table->string('rd_deleted_reason',255)->nullable();
            $table->unique('rd_code','tbl_rd_code_unique');
            $table->unique(['rd_dtc_or_id','rd_dtc_des_id'], 'tbl_rd_dtc_or_des_id_unique');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('route_delivery');
    }
}
