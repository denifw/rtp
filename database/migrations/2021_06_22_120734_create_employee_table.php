<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->uuid('em_id')->primary();
            $table->uuid('em_ss_id')->unsigned();
            $table->foreign('em_ss_id', 'tbl_em_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('em_cp_id')->unsigned();
            $table->foreign('em_cp_id', 'tbl_em_cp_id_fkey')->references('cp_id')->on('contact_person');
            $table->uuid('em_jt_id')->unsigned();
            $table->foreign('em_jt_id', 'tbl_em_jt_id_fkey')->references('jt_id')->on('job_title');
            $table->string('em_number', 128);
            $table->string('em_identity_number', 128);
            $table->string('em_phone', 128)->nullable();
            $table->string('em_email', 128)->nullable();
            $table->string('em_name', 256);
            $table->char('em_gender', 1);
            $table->date('em_birthday')->nullable();
            $table->date('em_join_date')->nullable();
            $table->char('em_active', 1)->default('Y');
            $table->uuid('em_created_by');
            $table->dateTime('em_created_on');
            $table->uuid('em_updated_by')->nullable();
            $table->dateTime('em_updated_on')->nullable();
            $table->uuid('em_deleted_by')->nullable();
            $table->dateTime('em_deleted_on')->nullable();
            $table->string('em_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee');
    }
}
