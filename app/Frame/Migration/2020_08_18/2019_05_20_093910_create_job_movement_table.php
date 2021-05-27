<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobMovementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::create('job_movement', function (Blueprint $table) {
            $table->bigIncrements('jm_id');
            $table->bigInteger('jm_jo_id')->unsigned();
            $table->foreign('jm_jo_id', 'tbl_jm_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jm_wh_id')->unsigned();
            $table->foreign('jm_wh_id', 'tbl_jm_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('jm_whs_id')->unsigned();
            $table->foreign('jm_whs_id', 'tbl_jm_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
            $table->date('jm_date');
            $table->time('jm_time');
            $table->dateTime('jm_complete_on')->nullable();
            $table->string('jm_remark', 255)->nullable();
            $table->bigInteger('jm_created_by');
            $table->dateTime('jm_created_on');
            $table->bigInteger('jm_updated_by')->nullable();
            $table->dateTime('jm_updated_on')->nullable();
            $table->bigInteger('jm_deleted_by')->nullable();
            $table->dateTime('jm_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_movement');
    }
}
