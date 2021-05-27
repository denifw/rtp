<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_detail', function (Blueprint $table) {
            $table->bigIncrements('qtnd_id');
            $table->bigInteger('qtnd_qtn_id')->unsigned();
            $table->foreign('qtnd_qtn_id', 'tbl_qtnd_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnd_cc_id')->unsigned();
            $table->foreign('qtnd_cc_id', 'tbl_qtnd_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->string('qtnd_description', 150);
            $table->float('qtnd_rate');
            $table->float('qtnd_quantity');
            $table->bigInteger('qtnd_uom_id')->unsigned()->nullable();
            $table->foreign('qtnd_uom_id', 'tbl_qtnd_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('qtnd_minimum_rate')->nullable();
            $table->bigInteger('qtnd_cur_id')->unsigned();
            $table->foreign('qtnd_cur_id', 'tbl_qtnd_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('qtnd_exchange_rate');
            $table->bigInteger('qtnd_tax_id')->unsigned();
            $table->foreign('qtnd_tax_id', 'tbl_qtnd_tax_id_foreign')->references('tax_id')->on('tax');
            $table->bigInteger('qtnd_created_by');
            $table->dateTime('qtnd_created_on');
            $table->bigInteger('qtnd_updated_by')->nullable();
            $table->dateTime('qtnd_updated_on')->nullable();
            $table->bigInteger('qtnd_deleted_by')->nullable();
            $table->dateTime('qtnd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_detail');
    }
}
