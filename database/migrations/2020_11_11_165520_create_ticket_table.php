<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket', function (Blueprint $table) {
            $table->bigIncrements('tc_id');
            $table->bigInteger('tc_ss_id')->unsigned();
            $table->foreign('tc_ss_id', 'tbl_tc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('tc_number', 256);
            $table->string('tc_subject', 256);
            $table->bigInteger('tc_rel_id')->unsigned();
            $table->foreign('tc_rel_id', 'tbl_tc_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('tc_pic_id')->unsigned()->nullable();
            $table->foreign('tc_pic_id', 'tbl_tc_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->date('tc_report_date');
            $table->time('tc_report_time');
            $table->bigInteger('tc_priority_id')->unsigned();
            $table->foreign('tc_priority_id', 'tbl_tc_priority_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('tc_status_id')->unsigned();
            $table->foreign('tc_status_id', 'tbl_tc_status_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('tc_assign_id')->unsigned();
            $table->foreign('tc_assign_id', 'tbl_tc_assign_id_foreign')->references('us_id')->on('users');
            $table->string('tc_description', 256)->nullable();
            $table->bigInteger('tc_start_by')->nullable();
            $table->foreign('tc_start_by', 'tbl_tc_start_by_foreign')->references('us_id')->on('users');
            $table->dateTime('tc_start_on')->nullable();
            $table->bigInteger('tc_finish_by')->nullable();
            $table->foreign('tc_finish_by', 'tbl_tc_finish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('tc_finish_on')->nullable();
            $table->bigInteger('tc_created_by');
            $table->dateTime('tc_created_on');
            $table->bigInteger('tc_updated_by')->nullable();
            $table->dateTime('tc_updated_on')->nullable();
            $table->bigInteger('tc_deleted_by')->nullable();
            $table->dateTime('tc_deleted_on')->nullable();
            $table->string('tc_deleted_reason', 256)->nullable();
            $table->uuid('tc_uid');
            $table->unique('tc_uid', 'tbl_tc_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket');
    }
}
