<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_order', function (Blueprint $table) {
            $table->uuid('jo_id')->primary();
            $table->uuid('jo_ss_id')->unsigned();
            $table->foreign('jo_ss_id', 'tbl_jo_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('jo_number', 128);
            $table->string('jo_name', 256);
            $table->uuid('jo_rel_id')->unsigned();
            $table->foreign('jo_rel_id', 'tbl_jo_rel_id_fkey')->references('rel_id')->on('relation');
            $table->uuid('jo_cp_id')->unsigned()->nullable();
            $table->foreign('jo_cp_id', 'tbl_jo_cp_id_fkey')->references('cp_id')->on('contact_person');
            $table->uuid('jo_srv_id')->unsigned();
            $table->foreign('jo_srv_id', 'tbl_jo_srv_id_fkey')->references('srv_id')->on('service');
            $table->float('jo_fee')->nullable();
            $table->float('jo_value')->nullable();
            $table->date('jo_estimation_start')->nullable();
            $table->date('jo_estimation_end')->nullable();
            $table->string('jo_address', 256)->nullable();
            $table->uuid('jo_dtc_id')->unsigned()->nullable();
            $table->string('jo_reference', 256)->nullable();
            $table->uuid('jo_us_id')->unsigned()->nullable();
            $table->foreign('jo_us_id', 'tbl_jo_us_id_fkey')->references('us_id')->on('users');

            $table->uuid('jo_publish_by')->unsigned()->nullable();
            $table->foreign('jo_publish_by', 'tbl_jo_publish_by_fkey')->references('us_id')->on('users');
            $table->dateTime('jo_publish_on')->nullable();
            $table->uuid('jo_start_by')->unsigned()->nullable();
            $table->foreign('jo_start_by', 'tbl_jo_start_by_fkey')->references('us_id')->on('users');
            $table->dateTime('jo_start_on')->nullable();
            $table->uuid('jo_finish_by')->unsigned()->nullable();
            $table->foreign('jo_finish_by', 'tbl_jo_finish_by_fkey')->references('us_id')->on('users');
            $table->dateTime('jo_finish_on')->nullable();


            $table->uuid('jo_created_by');
            $table->dateTime('jo_created_on');
            $table->uuid('jo_updated_by')->nullable();
            $table->dateTime('jo_updated_on')->nullable();
            $table->uuid('jo_deleted_by')->nullable();
            $table->dateTime('jo_deleted_on')->nullable();
            $table->string('jo_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_order');
    }
}
