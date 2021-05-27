<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_order', function (Blueprint $table) {
            $table->bigIncrements('svo_id');
            $table->bigInteger('svo_ss_id')->unsigned();
            $table->foreign('svo_ss_id', 'tbl_svo_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('svo_number', 255);
            $table->bigInteger('svo_eq_id')->unsigned();
            $table->foreign('svo_eq_id', 'tbl_svo_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->float('svo_meter')->nullable();
            $table->date('svo_order_date');
            $table->date('svo_planning_date');
            $table->bigInteger('svo_vendor_id')->unsigned();
            $table->foreign('svo_vendor_id', 'tbl_svo_vendor_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('svo_manager_id')->unsigned();
            $table->foreign('svo_manager_id', 'tbl_svo_manager_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('svo_request_by_id')->unsigned();
            $table->foreign('svo_request_by_id', 'tbl_svo_request_by_id_foreign')->references('us_id')->on('users');
            $table->string('svo_remark', 255)->nullable();
            $table->string('svo_deleted_reason', 255)->nullable();
            $table->bigInteger('svo_approved_by')->nullable();
            $table->dateTime('svo_approved_on')->nullable();
            $table->date('svo_start_service_date')->nullable();
            $table->time('svo_start_service_time')->nullable();
            $table->bigInteger('svo_start_service_by')->nullable();
            $table->bigInteger('svo_finish_by')->nullable();
            $table->dateTime('svo_finish_on')->nullable();
            $table->bigInteger('svo_created_by');
            $table->dateTime('svo_created_on');
            $table->bigInteger('svo_updated_by')->nullable();
            $table->dateTime('svo_updated_on')->nullable();
            $table->bigInteger('svo_deleted_by')->nullable();
            $table->dateTime('svo_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_order');
    }
}
