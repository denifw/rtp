<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_trucking', function (Blueprint $table) {
            $table->bigIncrements('jt_id');
            $table->bigInteger('jt_jo_id')->unsigned();
            $table->foreign('jt_jo_id', 'tbl_jt_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->date('jt_eta_date');
            $table->time('jt_eta_time');
            $table->bigInteger('jt_eg_id')->unsigned();
            $table->foreign('jt_eg_id', 'tbl_jt_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->bigInteger('jt_rel_id')->unsigned()->nullable();
            $table->foreign('jt_rel_id', 'tbl_jt_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jt_eq_id')->unsigned()->nullable();
            $table->foreign('jt_eq_id', 'tbl_jt_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->bigInteger('jt_first_cp')->unsigned()->nullable();
            $table->foreign('jt_first_cp', 'tbl_jt_first_cp_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jt_second_cp')->unsigned()->nullable();
            $table->foreign('jt_second_cp', 'tbl_jt_second_cp_foreign')->references('cp_id')->on('contact_person');

            $table->bigInteger('jt_ct_id')->unsigned()->nullable();
            $table->foreign('jt_ct_id', 'tbl_jt_ct_id_foreign')->references('ct_id')->on('container');
            $table->string('jt_container_number', 255)->nullable();
            $table->string('jt_seal_number', 255)->nullable();

            $table->bigInteger('jt_dp_id')->unsigned()->nullable();
            $table->foreign('jt_dp_id', 'tbl_jt_dp_id_foreign')->references('of_id')->on('office');
            $table->date('jt_pick_date')->nullable();
            $table->time('jt_pick_time')->nullable();
            $table->dateTime('jt_dp_eta')->nullable();
            $table->dateTime('jt_dp_ata')->nullable();
            $table->dateTime('jt_dp_start')->nullable();
            $table->dateTime('jt_dp_end')->nullable();
            $table->dateTime('jt_dp_atd')->nullable();

            $table->bigInteger('jt_dr_id')->unsigned()->nullable();
            $table->foreign('jt_dr_id', 'tbl_jt_dr_id_foreign')->references('of_id')->on('office');
            $table->date('jt_return_date')->nullable();
            $table->time('jt_return_time')->nullable();
            $table->dateTime('jt_dr_eta')->nullable();
            $table->dateTime('jt_dr_ata')->nullable();
            $table->dateTime('jt_dr_start')->nullable();
            $table->dateTime('jt_dr_end')->nullable();
            $table->dateTime('jt_dr_atd')->nullable();

            $table->dateTime('jt_start_load_on')->nullable();
            $table->dateTime('jt_end_load_on')->nullable();
            
            $table->dateTime('jt_start_unload_on')->nullable();
            $table->dateTime('jt_end_unload_on')->nullable();

            $table->bigInteger('jt_created_by');
            $table->dateTime('jt_created_on');
            $table->bigInteger('jt_updated_by')->nullable();
            $table->dateTime('jt_updated_on')->nullable();
            $table->bigInteger('jt_deleted_by')->nullable();
            $table->dateTime('jt_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_trucking');
    }
}
