<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobBundlingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_bundling_detail', function (Blueprint $table) {
            $table->bigIncrements('jbd_id');
            $table->bigInteger('jbd_jb_id')->unsigned();
            $table->foreign('jbd_jb_id', 'tbl_jbd_jb_id_foreign')->references('jb_id')->on('job_bundling');
            $table->bigInteger('jbd_jog_id')->unsigned();
            $table->foreign('jbd_jog_id', 'tbl_jbd_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->string('jbd_lot_number', 255)->nullable();
            $table->string('jbd_serial_number', 255)->nullable();
            $table->float('jbd_quantity');
            $table->bigInteger('jbd_us_id')->unsigned()->nullable();
            $table->foreign('jbd_us_id', 'tbl_jbd_us_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('jbd_adjust_by')->unsigned()->nullable();
            $table->foreign('jbd_adjust_by', 'tbl_jbd_adjust_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jbd_start_on')->nullable();
            $table->dateTime('jbd_end_on')->nullable();
            $table->bigInteger('jbd_created_by');
            $table->dateTime('jbd_created_on');
            $table->bigInteger('jbd_updated_by')->nullable();
            $table->dateTime('jbd_updated_on')->nullable();
            $table->bigInteger('jbd_deleted_by')->nullable();
            $table->dateTime('jbd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_bundling_detail');
    }
}
