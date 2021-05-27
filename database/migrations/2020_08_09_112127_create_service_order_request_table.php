<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceOrderRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_order_request', function (Blueprint $table) {
            $table->bigIncrements('svr_id');
            $table->bigInteger('svr_svo_id')->unsigned();
            $table->foreign('svr_svo_id', 'tbl_svr_svo_id_foreign')->references('svo_id')->on('service_order');
            $table->string('svr_reject_reason', 255)->nullable();
            $table->bigInteger('svr_created_by');
            $table->dateTime('svr_created_on');
            $table->bigInteger('svr_updated_by')->nullable();
            $table->dateTime('svr_updated_on')->nullable();
            $table->bigInteger('svr_deleted_by')->nullable();
            $table->dateTime('svr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_order_request');
    }
}
