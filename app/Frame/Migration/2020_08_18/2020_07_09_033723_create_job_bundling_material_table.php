<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobBundlingMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_bundling_material', function (Blueprint $table) {
            $table->bigIncrements('jbm_id');
            $table->bigInteger('jbm_jbd_id')->unsigned();
            $table->foreign('jbm_jbd_id', 'tbl_jbm_jbd_id_foreign')->references('jbd_id')->on('job_bundling_detail');

            $table->bigInteger('jbm_jog_id')->unsigned();
            $table->foreign('jbm_jog_id', 'tbl_jbm_jog_id_foreign')->references('jog_id')->on('job_goods');

            $table->string('jbm_lot_number', 255)->nullable();
            $table->string('jbm_serial_number', 255)->nullable();
            $table->float('jbm_quantity', 255)->nullable();

            $table->bigInteger('jbm_created_by');
            $table->dateTime('jbm_created_on');
            $table->bigInteger('jbm_updated_by')->nullable();
            $table->dateTime('jbm_updated_on')->nullable();
            $table->bigInteger('jbm_deleted_by')->nullable();
            $table->dateTime('jbm_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_bundling_material');
    }
}
