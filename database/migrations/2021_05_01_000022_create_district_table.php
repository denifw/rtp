<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('district', function (Blueprint $table) {
            $table->uuid('dtc_id')->primary();
            $table->uuid('dtc_cnt_id')->unsigned();
            $table->foreign('dtc_cnt_id', 'tbl_dtc_cnt_id_fkey')->references('cnt_id')->on('country');
            $table->uuid('dtc_stt_id')->unsigned();
            $table->foreign('dtc_stt_id', 'tbl_dtc_stt_id_fkey')->references('stt_id')->on('state');
            $table->uuid('dtc_cty_id')->unsigned();
            $table->foreign('dtc_cty_id', 'tbl_dtc_cty_id_fkey')->references('cty_id')->on('city');
            $table->string('dtc_name', 128);
            $table->string('dtc_iso', 64)->nullable();
            $table->char('dtc_active', 1)->default('Y');
            $table->uuid('dtc_created_by');
            $table->dateTime('dtc_created_on');
            $table->uuid('dtc_updated_by')->nullable();
            $table->dateTime('dtc_updated_on')->nullable();
            $table->uuid('dtc_deleted_by')->nullable();
            $table->dateTime('dtc_deleted_on')->nullable();
            $table->string('dtc_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('district');
    }
}
