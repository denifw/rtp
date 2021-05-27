<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_service', function (Blueprint $table) {
            $table->bigIncrements('sos_id');
            $table->bigInteger('sos_so_id')->unsigned();
            $table->foreign('sos_so_id', 'tbl_sos_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('sos_srt_id')->unsigned();
            $table->foreign('sos_srt_id', 'tbl_sos_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->bigInteger('sos_created_by');
            $table->dateTime('sos_created_on');
            $table->bigInteger('sos_updated_by')->nullable();
            $table->dateTime('sos_updated_on')->nullable();
            $table->bigInteger('sos_deleted_by')->nullable();
            $table->dateTime('sos_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_service');
    }
}
