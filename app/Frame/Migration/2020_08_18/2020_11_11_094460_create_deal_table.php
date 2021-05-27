<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal', function (Blueprint $table) {
            $table->bigIncrements('dl_id');
            $table->bigInteger('dl_ss_id')->unsigned();
            $table->foreign('dl_ss_id', 'tbl_dl_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('dl_number', 256);
            $table->string('dl_name', 256);
            $table->bigInteger('dl_rel_id')->unsigned();
            $table->foreign('dl_rel_id', 'tbl_dl_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('dl_pic_id')->unsigned()->nullable();
            $table->foreign('dl_pic_id', 'tbl_dl_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('dl_manager_id')->unsigned();
            $table->foreign('dl_manager_id', 'tbl_dl_manager_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('dl_source_id')->unsigned()->nullable();
            $table->foreign('dl_source_id', 'tbl_dl_source_id_foreign')->references('sty_id')->on('system_type');
            $table->float('dl_amount');
            $table->date('dl_close_date')->nullable();
            $table->bigInteger('dl_stage_id')->unsigned();
            $table->foreign('dl_stage_id', 'tbl_dl_stage_id_foreign')->references('sty_id')->on('system_type');
            $table->string('dl_description', 256)->nullable();
            $table->bigInteger('dl_created_by');
            $table->dateTime('dl_created_on');
            $table->bigInteger('dl_updated_by')->nullable();
            $table->dateTime('dl_updated_on')->nullable();
            $table->bigInteger('dl_deleted_by')->nullable();
            $table->dateTime('dl_deleted_on')->nullable();
            $table->string('dl_deleted_reason', 256)->nullable();
            $table->uuid('dl_uid');
            $table->unique('dl_uid', 'tbl_dl_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deal');
    }
}
