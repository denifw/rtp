<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->bigIncrements('tsk_id');
            $table->bigInteger('tsk_ss_id')->unsigned();
            $table->foreign('tsk_ss_id', 'tbl_tsk_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('tsk_number', 256);
            $table->string('tsk_subject', 256);
            $table->bigInteger('tsk_rel_id')->unsigned();
            $table->foreign('tsk_rel_id', 'tbl_tsk_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('tsk_pic_id')->unsigned()->nullable();
            $table->foreign('tsk_pic_id', 'tbl_tsk_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('tsk_type_id')->unsigned();
            $table->foreign('tsk_type_id', 'tbl_tsk_type_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('tsk_priority_id')->unsigned();
            $table->foreign('tsk_priority_id', 'tbl_tsk_priority_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('tsk_status_id')->unsigned();
            $table->foreign('tsk_status_id', 'tbl_tsk_status_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('tsk_assign_id')->unsigned();
            $table->foreign('tsk_assign_id', 'tbl_tsk_assign_id_foreign')->references('us_id')->on('users');
            $table->string('tsk_location', 256)->nullable();
            $table->bigInteger('tsk_dl_id')->unsigned()->nullable();
            $table->foreign('tsk_dl_id', 'tbl_tsk_dl_id_foreign')->references('dl_id')->on('deal');
            $table->date('tsk_start_date')->nullable();
            $table->time('tsk_start_time')->nullable();
            $table->date('tsk_end_date')->nullable();
            $table->time('tsk_end_time')->nullable();
            $table->text('tsk_result')->nullable();
            $table->text('tsk_description')->nullable();
            $table->text('tsk_next_step')->nullable();
            $table->bigInteger('tsk_start_by')->nullable();
            $table->foreign('tsk_start_by', 'tbl_tsk_start_by_foreign')->references('us_id')->on('users');
            $table->dateTime('tsk_start_on')->nullable();
            $table->bigInteger('tsk_finish_by')->nullable();
            $table->foreign('tsk_finish_by', 'tbl_tsk_finish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('tsk_finish_on')->nullable();
            $table->bigInteger('tsk_created_by');
            $table->dateTime('tsk_created_on');
            $table->bigInteger('tsk_updated_by')->nullable();
            $table->dateTime('tsk_updated_on')->nullable();
            $table->bigInteger('tsk_deleted_by')->nullable();
            $table->dateTime('tsk_deleted_on')->nullable();
            $table->string('tsk_deleted_reason', 256)->nullable();
            $table->uuid('tsk_uid');
            $table->unique('tsk_uid', 'tbl_tsk_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}
