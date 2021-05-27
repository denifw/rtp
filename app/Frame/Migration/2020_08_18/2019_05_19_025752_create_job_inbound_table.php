<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inbound', function (Blueprint $table) {
            $table->bigIncrements('ji_id');
            $table->bigInteger('ji_jo_id')->unsigned();
            $table->foreign('ji_jo_id', 'tbl_ji_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('ji_wh_id')->unsigned();
            $table->foreign('ji_wh_id', 'tbl_ji_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->date('ji_eta_date');
            $table->time('ji_eta_time');
            $table->date('ji_ata_date')->nullable();
            $table->time('ji_ata_time')->nullable();
            $table->bigInteger('ji_rel_id')->unsigned()->nullable();
            $table->foreign('ji_rel_id', 'tbl_ji_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('ji_of_id')->unsigned()->nullable();
            $table->foreign('ji_of_id', 'tbl_ji_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('ji_cp_id')->unsigned()->nullable();
            $table->foreign('ji_cp_id', 'tbl_ji_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('ji_vendor_id')->unsigned()->nullable();
            $table->foreign('ji_vendor_id', 'tbl_ji_vendor_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('ji_pic_vendor')->unsigned()->nullable();
            $table->foreign('ji_pic_vendor', 'tbl_ji_pic_vendor_foreign')->references('cp_id')->on('contact_person');
            $table->string('ji_truck_number')->nullable();
            $table->string('ji_container_number')->nullable();
            $table->string('ji_seal_number')->nullable();
            $table->dateTime('ji_start_load_on')->nullable();
            $table->dateTime('ji_end_load_on')->nullable();
            $table->dateTime('ji_start_store_on')->nullable();
            $table->dateTime('ji_end_store_on')->nullable();
            $table->bigInteger('ji_created_by');
            $table->dateTime('ji_created_on');
            $table->bigInteger('ji_updated_by')->nullable();
            $table->dateTime('ji_updated_on')->nullable();
            $table->bigInteger('ji_deleted_by')->nullable();
            $table->dateTime('ji_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inbound');
    }
}
