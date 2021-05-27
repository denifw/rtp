<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOutboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_outbound', function (Blueprint $table) {
            $table->bigIncrements('job_id');
            $table->bigInteger('job_jo_id')->unsigned();
            $table->foreign('job_jo_id', 'tbl_job_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('job_wh_id')->unsigned();
            $table->foreign('job_wh_id', 'tbl_job_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->date('job_eta_date');
            $table->time('job_eta_time');
            $table->date('job_ata_date')->nullable();
            $table->time('job_ata_time')->nullable();
            $table->bigInteger('job_rel_id')->unsigned()->nullable();
            $table->foreign('job_rel_id', 'tbl_job_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('job_of_id')->unsigned()->nullable();
            $table->foreign('job_of_id', 'tbl_job_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('job_cp_id')->unsigned()->nullable();
            $table->foreign('job_cp_id', 'tbl_job_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('job_vendor_id')->unsigned()->nullable();
            $table->foreign('job_vendor_id', 'tbl_job_vendor_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('job_pic_vendor')->unsigned()->nullable();
            $table->foreign('job_pic_vendor', 'tbl_job_pic_vendor_foreign')->references('cp_id')->on('contact_person');
            $table->string('job_truck_number')->nullable();
            $table->string('job_container_number')->nullable();
            $table->string('job_seal_number')->nullable();
            $table->dateTime('job_start_load_on')->nullable();
            $table->dateTime('job_end_load_on')->nullable();
            $table->dateTime('job_start_store_on')->nullable();
            $table->dateTime('job_end_store_on')->nullable();
            $table->bigInteger('job_created_by');
            $table->dateTime('job_created_on');
            $table->bigInteger('job_updated_by')->nullable();
            $table->dateTime('job_updated_on')->nullable();
            $table->bigInteger('job_deleted_by')->nullable();
            $table->dateTime('job_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_outbound');
    }
}
