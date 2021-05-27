<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_warehouse', function (Blueprint $table) {
            $table->bigIncrements('qtnw_id');
            $table->bigInteger('qtnw_qtn_id')->unsigned();
            $table->foreign('qtnw_qtn_id', 'tbl_qtnw_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnw_wh_id')->unsigned();
            $table->foreign('qtnw_wh_id', 'tbl_qtnw_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('qtnw_created_by');
            $table->dateTime('qtnw_created_on');
            $table->bigInteger('qtnw_updated_by')->nullable();
            $table->dateTime('qtnw_updated_on')->nullable();
            $table->bigInteger('qtnw_deleted_by')->nullable();
            $table->dateTime('qtnw_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_warehouse');
    }
}
