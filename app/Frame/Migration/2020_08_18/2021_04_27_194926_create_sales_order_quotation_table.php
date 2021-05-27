<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_quotation', function (Blueprint $table) {
            $table->bigIncrements('soq_id');
            $table->bigInteger('soq_so_id')->unsigned();
            $table->foreign('soq_so_id', 'tbl_soq_so_id_fkey')->references('so_id')->on('sales_order');
            $table->bigInteger('soq_qt_id')->unsigned();
            $table->foreign('soq_qt_id', 'tbl_soq_qt_id_fkey')->references('qt_id')->on('quotation');
            $table->bigInteger('soq_created_by');
            $table->dateTime('soq_created_on');
            $table->bigInteger('soq_updated_by')->nullable();
            $table->dateTime('soq_updated_on')->nullable();
            $table->bigInteger('soq_deleted_by')->nullable();
            $table->dateTime('soq_deleted_on')->nullable();
            $table->string('soq_deleted_reason', 256)->nullable();
            $table->uuid('soq_uid');
            $table->unique('soq_uid', 'tbl_soq_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_quotation');
    }
}
