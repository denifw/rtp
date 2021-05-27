<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobAdjustmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_adjustment', function (Blueprint $table) {
            $table->bigIncrements('ja_id');
            $table->bigInteger('ja_jo_id')->unsigned();
            $table->foreign('ja_jo_id', 'tbl_ja_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('ja_wh_id')->unsigned();
            $table->foreign('ja_wh_id', 'tbl_ja_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->dateTime('ja_complete_on')->nullable();
            $table->bigInteger('ja_created_by');
            $table->dateTime('ja_created_on');
            $table->bigInteger('ja_updated_by')->nullable();
            $table->dateTime('ja_updated_on')->nullable();
            $table->bigInteger('ja_deleted_by')->nullable();
            $table->dateTime('ja_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_adjustment');
    }
}
