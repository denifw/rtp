<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_received', function (Blueprint $table) {
            $table->bigIncrements('carc_id');
            $table->bigInteger('carc_ca_id')->unsigned();
            $table->foreign('carc_ca_id', 'tbl_carc_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->string('carc_reject_reason', 255)->nullable();
            $table->bigInteger('carc_created_by');
            $table->dateTime('carc_created_on');
            $table->bigInteger('carc_updated_by')->nullable();
            $table->dateTime('carc_updated_on')->nullable();
            $table->bigInteger('carc_deleted_by')->nullable();
            $table->dateTime('carc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance_received');
    }
}
