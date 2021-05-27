<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */ 
    public function up()
    {
        Schema::create('job_sales', function (Blueprint $table) {
            $table->bigIncrements('jos_id');
            $table->bigInteger('jos_jo_id')->unsigned();
            $table->foreign('jos_jo_id', 'tbl_jos_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jos_cc_id')->unsigned();
            $table->foreign('jos_cc_id', 'tbl_jos_cc_id')->references('cc_id')->on('cost_code');
            $table->bigInteger('jos_rel_id')->unsigned();
            $table->foreign('jos_rel_id', 'tbl_jos_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('jos_description', 150);
            $table->float('jos_rate');
            $table->float('jos_minimum_rate')->nullable();
            $table->float('jos_quantity');
            $table->bigInteger('jos_uom_id')->unsigned();
            $table->foreign('jos_uom_id', 'tbl_jos_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jos_cur_id')->unsigned();
            $table->foreign('jos_cur_id', 'tbl_jos_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('jos_exchange_rate');
            $table->bigInteger('jos_tax_id')->unsigned();
            $table->foreign('jos_tax_id', 'tbl_jos_tax_id_foreign')->references('tax_id')->on('tax');
            $table->bigInteger('jos_qtnd_id')->unsigned()->nullable();
            $table->foreign('jos_qtnd_id', 'tbl_jos_qtnd_id_foreign')->references('qtnd_id')->on('quotation_detail');
            $table->bigInteger('jos_created_by');
            $table->dateTime('jos_created_on');
            $table->bigInteger('jos_updated_by')->nullable();
            $table->dateTime('jos_updated_on')->nullable();
            $table->bigInteger('jos_deleted_by')->nullable();
            $table->dateTime('jos_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_sales');
    }
}
