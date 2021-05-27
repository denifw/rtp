<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreatePettyCashRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petty_cash_request', function (Blueprint $table) {
            $table->bigIncrements('pcr_id');
            $table->bigInteger('pcr_ptc_id')->unsigned();
            $table->foreign('pcr_ptc_id', 'tbl_pcr_ptc_id_foreign')->references('ptc_id')->on('petty_cash');
            $table->string('pcr_reject_reason', 255)->nullable();
            $table->bigInteger('pcr_created_by');
            $table->dateTime('pcr_created_on');
            $table->bigInteger('pcr_updated_by')->nullable();
            $table->dateTime('pcr_updated_on')->nullable();
            $table->bigInteger('pcr_deleted_by')->nullable();
            $table->dateTime('pcr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petty_cash_request');
    }
}
