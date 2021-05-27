<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalOrderRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_order_request', function (Blueprint $table) {
            $table->bigIncrements('rnr_id');
            $table->bigInteger('rnr_rno_id')->unsigned();
            $table->foreign('rnr_rno_id', 'tbl_rnr_rno_id_foreign')->references('rno_id')->on('renewal_order');
            $table->string('rnr_reject_reason', 255)->nullable();
            $table->bigInteger('rnr_created_by');
            $table->dateTime('rnr_created_on');
            $table->bigInteger('rnr_updated_by')->nullable();
            $table->dateTime('rnr_updated_on')->nullable();
            $table->bigInteger('rnr_deleted_by')->nullable();
            $table->dateTime('rnr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_order_request');
    }
}
