<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_detail', function (Blueprint $table) {
            $table->bigIncrements('cad_id');
            $table->bigInteger('cad_ca_id')->unsigned();
            $table->foreign('cad_ca_id', 'tbl_cad_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->bigInteger('cad_jop_id')->unsigned();
            $table->foreign('cad_jop_id', 'tbl_cad_jop_id_foreign')->references('jop_id')->on('job_purchase');
            $table->bigInteger('cad_created_by');
            $table->dateTime('cad_created_on');
            $table->bigInteger('cad_updated_by')->nullable();
            $table->dateTime('cad_updated_on')->nullable();
            $table->bigInteger('cad_deleted_by')->nullable();
            $table->dateTime('cad_deleted_on')->nullable();
            $table->string('cad_deleted_reason', 256)->nullable();
            $table->uuid('cad_uid');
            $table->unique('cad_uid', 'tbl_cad_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance_detail');
    }
}
