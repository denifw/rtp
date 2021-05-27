<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_delivery', function (Blueprint $table) {
            $table->bigIncrements('jdl_id');
            $table->bigInteger('jdl_jo_id')->unsigned();
            $table->foreign('jdl_jo_id', 'tbl_jdl_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jdl_so_id')->unsigned()->nullable();
            $table->foreign('jdl_so_id', 'tbl_jdl_so_id_foreign')->references('so_id')->on('sales_order');
            $table->date('jdl_departure_date');
            $table->time('jdl_departure_time');
            $table->date('jdl_arrival_date')->nullable();
            $table->time('jdl_arrival_time')->nullable();
            $table->char('jdl_consolidate', 1)->default('N');
            $table->bigInteger('jdl_tm_id')->unsigned();
            $table->foreign('jdl_tm_id', 'tbl_jdl_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->bigInteger('jdl_eg_id')->unsigned()->nullable();
            $table->foreign('jdl_eg_id', 'tbl_jdl_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->bigInteger('jdl_pol_id')->unsigned()->nullable();
            $table->foreign('jdl_pol_id', 'tbl_jdl_pol_id_foreign')->references('po_id')->on('port');
            $table->bigInteger('jdl_pod_id')->unsigned()->nullable();
            $table->foreign('jdl_pod_id', 'tbl_jdl_pod_id_foreign')->references('po_id')->on('port');
            $table->bigInteger('jdl_eq_id')->unsigned()->nullable();
            $table->foreign('jdl_eq_id', 'tbl_jdl_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->string('jdl_transport_number', 128)->nullable();
            $table->bigInteger('jdl_first_cp_id')->unsigned()->nullable();
            $table->foreign('jdl_first_cp_id', 'tbl_jdl_first_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jdl_second_cp_id')->unsigned()->nullable();
            $table->foreign('jdl_second_cp_id', 'tbl_jdl_second_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jdl_ct_id')->unsigned()->nullable();
            $table->foreign('jdl_ct_id', 'tbl_jdl_ct_id_foreign')->references('ct_id')->on('container');
            $table->string('jdl_container_number', 128)->nullable();
            $table->string('jdl_seal_number', 128)->nullable();
            $table->bigInteger('jdl_dp_id')->unsigned()->nullable();
            $table->foreign('jdl_dp_id', 'tbl_jdl_dp_id_foreign')->references('of_id')->on('office');
            $table->date('jdl_dp_date')->nullable();
            $table->time('jdl_dp_time')->nullable();
            $table->dateTime('jdl_dp_ata')->nullable();
            $table->dateTime('jdl_dp_start')->nullable();
            $table->dateTime('jdl_dp_end')->nullable();
            $table->dateTime('jdl_dp_atd')->nullable();
            $table->bigInteger('jdl_dr_id')->unsigned()->nullable();
            $table->foreign('jdl_dr_id', 'tbl_jdl_dr_id_foreign')->references('of_id')->on('office');
            $table->date('jdl_dr_date')->nullable();
            $table->time('jdl_dr_time')->nullable();
            $table->dateTime('jdl_dr_ata')->nullable();
            $table->dateTime('jdl_dr_start')->nullable();
            $table->dateTime('jdl_dr_end')->nullable();
            $table->dateTime('jdl_dr_atd')->nullable();
            $table->bigInteger('jdl_created_by');
            $table->dateTime('jdl_created_on');
            $table->bigInteger('jdl_updated_by')->nullable();
            $table->dateTime('jdl_updated_on')->nullable();
            $table->bigInteger('jdl_deleted_by')->nullable();
            $table->dateTime('jdl_deleted_on')->nullable();
            $table->string('jdl_deleted_reason', 256)->nullable();
            $table->uuid('jdl_uid');
            $table->unique('jdl_uid', 'tbl_jdl_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_delivery');
    }
}
