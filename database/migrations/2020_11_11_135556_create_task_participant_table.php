<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_participant', function (Blueprint $table) {
            $table->bigIncrements('tp_id');
            $table->bigInteger('tp_tsk_id')->unsigned();
            $table->foreign('tp_tsk_id', 'tbl_tp_tsk_id_foreign')->references('tsk_id')->on('task');
            $table->bigInteger('tp_rel_id')->unsigned();
            $table->foreign('tp_rel_id', 'tbl_tp_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('tp_cp_id')->unsigned();
            $table->foreign('tp_cp_id', 'tbl_tp_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('tp_created_by');
            $table->dateTime('tp_created_on');
            $table->bigInteger('tp_updated_by')->nullable();
            $table->dateTime('tp_updated_on')->nullable();
            $table->bigInteger('tp_deleted_by')->nullable();
            $table->dateTime('tp_deleted_on')->nullable();
            $table->string('tp_deleted_reason', 256)->nullable();
            $table->uuid('tp_uid');
            $table->unique('tp_uid', 'tbl_tp_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_participant');
    }
}
