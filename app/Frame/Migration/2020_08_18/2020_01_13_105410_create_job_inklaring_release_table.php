<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInklaringReleaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inklaring_release', function (Blueprint $table) {
            $table->bigIncrements('jikr_id');
            $table->bigInteger('jikr_jik_id')->unsigned();
            $table->foreign('jikr_jik_id', 'tbl_jikr_jik_id_foreign')->references('jik_id')->on('job_inklaring');
            $table->bigInteger('jikr_joc_id')->unsigned()->nullable();
            $table->foreign('jikr_joc_id', 'tbl_jikr_joc_id_foreign')->references('joc_id')->on('job_container');
            $table->bigInteger('jikr_jog_id')->unsigned()->nullable();
            $table->foreign('jikr_jog_id', 'tbl_jikr_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->float('jikr_quantity');
            $table->bigInteger('jikr_uom_id')->unsigned()->nullable();
            $table->foreign('jikr_uom_id', 'tbl_jikr_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jikr_ct_id')->unsigned()->nullable();
            $table->foreign('jikr_ct_id', 'tbl_jikr_ct_id_foreign')->references('ct_id')->on('container');
            $table->bigInteger('jikr_transporter_id')->unsigned();
            $table->foreign('jikr_transporter_id', 'tbl_jikr_transporter_id_foreign')->references('rel_id')->on('relation');
            $table->string('jikr_truck_number', 255);
            $table->string('jikr_driver', 255)->nullable();
            $table->string('jikr_driver_phone', 255)->nullable();
            $table->date('jikr_load_date');
            $table->time('jikr_load_time');
            $table->bigInteger('jikr_load_by');
            $table->date('jikr_gate_in_date')->nullable();
            $table->time('jikr_gate_in_time')->nullable();
            $table->bigInteger('jikr_gate_in_by')->nullable();
            $table->bigInteger('jikr_created_by');
            $table->dateTime('jikr_created_on');
            $table->bigInteger('jikr_updated_by')->nullable();
            $table->dateTime('jikr_updated_on')->nullable();
            $table->bigInteger('jikr_deleted_by')->nullable();
            $table->dateTime('jikr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inklaring_release');
    }
}
