<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobBundlingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_bundling', function (Blueprint $table) {
            $table->bigIncrements('jb_id');
            $table->bigInteger('jb_jo_id')->unsigned();
            $table->foreign('jb_jo_id', 'tbl_jb_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jb_wh_id')->unsigned();
            $table->foreign('jb_wh_id', 'tbl_jb_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('jb_jog_id')->unsigned();
            $table->foreign('jb_jog_id', 'tbl_jb_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->date('jb_et_date');
            $table->time('jb_et_time');
            $table->dateTime('jb_start_pick_on')->nullable();
            $table->dateTime('jb_end_pick_on')->nullable();
            $table->dateTime('jb_start_pack_on')->nullable();
            $table->dateTime('jb_end_pack_on')->nullable();
            $table->dateTime('jb_start_store_on')->nullable();
            $table->dateTime('jb_end_store_on')->nullable();
            $table->bigInteger('jb_created_by');
            $table->dateTime('jb_created_on');
            $table->bigInteger('jb_updated_by')->nullable();
            $table->dateTime('jb_updated_on')->nullable();
            $table->bigInteger('jb_deleted_by')->nullable();
            $table->dateTime('jb_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_bundling');
    }
}
