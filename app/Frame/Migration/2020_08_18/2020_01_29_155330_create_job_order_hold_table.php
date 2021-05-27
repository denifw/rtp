<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderHoldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_order_hold', function (Blueprint $table) {
            $table->bigIncrements('joh_id');
            $table->bigInteger('joh_jo_id')->unsigned();
            $table->foreign('joh_jo_id', 'tbl_joh_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->string('joh_reason', 255);
            $table->bigInteger('joh_created_by');
            $table->dateTime('joh_created_on');
            $table->bigInteger('joh_updated_by')->nullable();
            $table->dateTime('joh_updated_on')->nullable();
            $table->bigInteger('joh_deleted_by')->nullable();
            $table->dateTime('joh_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_order_hold');
    }
}
