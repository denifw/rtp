<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistrictCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('district_code', function (Blueprint $table) {
            $table->bigIncrements('dtcc_id');
            $table->bigInteger('dtcc_ss_id')->unsigned();
            $table->foreign('dtcc_ss_id', 'tbl_dtcc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('dtcc_dtc_id')->unsigned();
            $table->foreign('dtcc_dtc_id', 'tbl_dtcc_dtc_id_foreign')->references('dtc_id')->on('district');
            $table->string('dtcc_code', 128)->nullable();
            $table->bigInteger('dtcc_created_by');
            $table->dateTime('dtcc_created_on');
            $table->bigInteger('dtcc_updated_by')->nullable();
            $table->dateTime('dtcc_updated_on')->nullable();
            $table->bigInteger('dtcc_deleted_by')->nullable();
            $table->dateTime('dtcc_deleted_on')->nullable();
            $table->string('dtcc_deleted_reason', 256)->nullable();
            $table->uuid('dtcc_uid');
            $table->unique('dtcc_uid', 'tbl_dtcc_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('district_code');
    }
}
