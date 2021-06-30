<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice_detail', function (Blueprint $table) {
            $table->uuid('sid_id')->primary();
            $table->uuid('sid_si_id')->unsigned();
            $table->foreign('sid_si_id', 'tbl_sid_si_id_foreign')->references('si_id')->on('sales_invoice');
            $table->uuid('sid_cc_id')->unsigned();
            $table->foreign('sid_cc_id', 'tbl_sid_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->string('sid_description', 256);
            $table->float('sid_quantity');
            $table->uuid('sid_uom_id')->unsigned();
            $table->foreign('sid_uom_id', 'tbl_sid_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('sid_rate');
            $table->uuid('sid_cur_id')->unsigned();
            $table->foreign('sid_cur_id', 'tbl_sid_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('sid_exchange_rate');
            $table->uuid('sid_tax_id')->unsigned();
            $table->foreign('sid_tax_id', 'tbl_sid_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('sid_total');
            $table->uuid('sid_created_by');
            $table->dateTime('sid_created_on');
            $table->uuid('sid_updated_by')->nullable();
            $table->dateTime('sid_updated_on')->nullable();
            $table->uuid('sid_deleted_by')->nullable();
            $table->dateTime('sid_deleted_on')->nullable();
            $table->string('sid_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice_detail');
    }
}
