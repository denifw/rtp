<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderHoldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_hold', function (Blueprint $table) {
            $table->bigIncrements('soh_id');
            $table->bigInteger('soh_so_id')->unsigned();
            $table->foreign('soh_so_id', 'tbl_soh_so_id_foreign')->references('so_id')->on('sales_order');
            $table->string('soh_reason', 255);
            $table->bigInteger('soh_created_by');
            $table->dateTime('soh_created_on');
            $table->bigInteger('soh_updated_by')->nullable();
            $table->dateTime('soh_updated_on')->nullable();
            $table->bigInteger('soh_deleted_by')->nullable();
            $table->dateTime('soh_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_hold');
    }
}
