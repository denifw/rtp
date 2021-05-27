<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_purchase', function (Blueprint $table) {
            $table->bigIncrements('jop_id');
            $table->bigInteger('jop_jo_id')->unsigned();
            $table->foreign('jop_jo_id', 'tbl_jop_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jop_cc_id')->unsigned();
            $table->foreign('jop_cc_id', 'tbl_jop_cc_id')->references('cc_id')->on('cost_code');
            $table->bigInteger('jop_rel_id')->unsigned();
            $table->foreign('jop_rel_id', 'tbl_jop_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('jop_description', 150);
            $table->float('jop_rate');
            $table->float('jop_minimum_rate')->nullable();
            $table->float('jop_quantity');
            $table->bigInteger('jop_uom_id')->unsigned();
            $table->foreign('jop_uom_id', 'tbl_jop_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jop_cur_id')->unsigned();
            $table->foreign('jop_cur_id', 'tbl_jop_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('jop_exchange_rate');
            $table->bigInteger('jop_tax_id')->unsigned();
            $table->foreign('jop_tax_id', 'tbl_jop_tax_id_foreign')->references('tax_id')->on('tax');
            $table->bigInteger('jop_qtnd_id')->unsigned()->nullable();
            $table->foreign('jop_qtnd_id', 'tbl_jop_qtnd_id_foreign')->references('qtnd_id')->on('quotation_detail');
            $table->bigInteger('jop_created_by');
            $table->dateTime('jop_created_on');
            $table->bigInteger('jop_updated_by')->nullable();
            $table->dateTime('jop_updated_on')->nullable();
            $table->bigInteger('jop_deleted_by')->nullable();
            $table->dateTime('jop_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_purchase');
    }
}
