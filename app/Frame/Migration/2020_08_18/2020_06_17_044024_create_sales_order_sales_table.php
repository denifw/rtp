<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_sales', function (Blueprint $table) {
            $table->bigIncrements('sosl_id');
            $table->bigInteger('sosl_so_id')->unsigned();
            $table->foreign('sosl_so_id', 'tbl_sosl_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('sosl_cc_id')->unsigned();
            $table->foreign('sosl_cc_id', 'tbl_sosl_cc_id')->references('cc_id')->on('cost_code');
            $table->bigInteger('sosl_rel_id')->unsigned();
            $table->foreign('sosl_rel_id', 'tbl_sosl_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('sosl_description', 150);
            $table->float('sosl_rate');
            $table->float('sosl_quantity');
            $table->bigInteger('sosl_uom_id')->unsigned();
            $table->foreign('sosl_uom_id', 'tbl_sosl_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('sosl_cur_id')->unsigned();
            $table->foreign('sosl_cur_id', 'tbl_sosl_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('sosl_exchange_rate');
            $table->bigInteger('sosl_tax_id')->unsigned();
            $table->foreign('sosl_tax_id', 'tbl_sosl_tax_id_foreign')->references('tax_id')->on('tax');
            $table->bigInteger('sosl_created_by');
            $table->dateTime('sosl_created_on');
            $table->bigInteger('sosl_updated_by')->nullable();
            $table->dateTime('sosl_updated_on')->nullable();
            $table->bigInteger('sosl_deleted_by')->nullable();
            $table->dateTime('sosl_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_sales');
    }
}
