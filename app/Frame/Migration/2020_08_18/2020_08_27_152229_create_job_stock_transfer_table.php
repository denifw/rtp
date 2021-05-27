<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobStockTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_stock_transfer', function (Blueprint $table) {
            $table->bigIncrements('jtr_id');
            $table->string('jtr_number', 255);
            $table->bigInteger('jtr_ss_id')->unsigned();
            $table->foreign('jtr_ss_id', 'tbl_jtr_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('jtr_rel_id')->unsigned();
            $table->foreign('jtr_rel_id', 'tbl_jtr_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('jtr_customer_ref', 255)->nullable();
            $table->bigInteger('jtr_pic_id')->unsigned()->nullable();
            $table->foreign('jtr_pic_id', 'tbl_jtr_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jtr_who_id')->unsigned();
            $table->foreign('jtr_who_id', 'tbl_jtr_who_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('jtr_who_us_id')->unsigned();
            $table->foreign('jtr_who_us_id', 'tbl_jtr_who_us_id_foreign')->references('us_id')->on('users');
            $table->date('jtr_who_date');
            $table->time('jtr_who_time');
            $table->bigInteger('jtr_whd_id')->unsigned();
            $table->foreign('jtr_whd_id', 'tbl_jtr_whd_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('jtr_whd_us_id')->unsigned();
            $table->foreign('jtr_whd_us_id', 'tbl_jtr_whd_us_id_foreign')->references('us_id')->on('users');
            $table->date('jtr_whd_date');
            $table->time('jtr_whd_time');
            $table->bigInteger('jtr_transporter_id')->unsigned();
            $table->foreign('jtr_transporter_id', 'tbl_jtr_transporter_id_foreign')->references('rel_id')->on('relation');
            $table->string('jtr_truck_plate', 255)->nullable();
            $table->string('jtr_container_number', 255)->nullable();
            $table->string('jtr_seal_number', 255)->nullable();
            $table->string('jtr_driver', 255)->nullable();
            $table->string('jtr_driver_phone', 255)->nullable();
            $table->bigInteger('jtr_ji_jo_id')->unsigned()->nullable();
            $table->foreign('jtr_ji_jo_id', 'tbl_jtr_ji_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jtr_job_jo_id')->unsigned()->nullable();
            $table->foreign('jtr_job_jo_id', 'tbl_jtr_job_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jtr_publish_by')->unsigned()->nullable();
            $table->foreign('jtr_publish_by', 'tbl_jtr_publish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jtr_publish_on')->nullable();
            $table->dateTime('jtr_start_out_on')->nullable();
            $table->dateTime('jtr_end_out_on')->nullable();
            $table->dateTime('jtr_start_in_on')->nullable();
            $table->dateTime('jtr_end_in_on')->nullable();
            $table->string('jtr_deleted_reason', 255)->nullable();
            $table->bigInteger('jtr_created_by');
            $table->dateTime('jtr_created_on');
            $table->bigInteger('jtr_updated_by')->nullable();
            $table->dateTime('jtr_updated_on')->nullable();
            $table->bigInteger('jtr_deleted_by')->nullable();
            $table->dateTime('jtr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_stock_transfer');
    }
}
