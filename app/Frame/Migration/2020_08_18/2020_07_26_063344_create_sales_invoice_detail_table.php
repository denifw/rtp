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
            $table->bigIncrements('sid_id');
            $table->bigInteger('sid_si_id')->unsigned();
            $table->foreign('sid_si_id', 'tbl_sid_si_id_foreign')->references('si_id')->on('sales_invoice');
            $table->bigInteger('sid_jos_id')->unsigned()->nullable();
            $table->foreign('sid_jos_id', 'tbl_sid_jos_id_foreign')->references('jos_id')->on('job_sales');
            $table->bigInteger('sid_cc_id')->unsigned()->nullable();
            $table->foreign('sid_cc_id', 'tbl_sid_cc_id')->references('cc_id')->on('cost_code');
            $table->string('sid_description', 150)->nullable();
            $table->float('sid_rate')->nullable();
            $table->float('sid_quantity')->nullable();
            $table->bigInteger('sid_uom_id')->unsigned()->nullable();
            $table->foreign('sid_uom_id', 'tbl_sid_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('sid_cur_id')->unsigned()->nullable();
            $table->foreign('sid_cur_id', 'tbl_sid_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('sid_exchange_rate')->nullable();
            $table->bigInteger('sid_tax_id')->unsigned()->nullable();
            $table->foreign('sid_tax_id', 'tbl_sid_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('sid_total')->nullable();
            $table->bigInteger('sid_created_by');
            $table->dateTime('sid_created_on');
            $table->bigInteger('sid_updated_by')->nullable();
            $table->dateTime('sid_updated_on')->nullable();
            $table->bigInteger('sid_deleted_by')->nullable();
            $table->dateTime('sid_deleted_on')->nullable();
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
