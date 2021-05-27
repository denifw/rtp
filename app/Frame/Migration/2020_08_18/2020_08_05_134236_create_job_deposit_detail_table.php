<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDepositDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_deposit_detail', function (Blueprint $table) {
            $table->bigIncrements('jdd_id');
            $table->bigInteger('jdd_jd_id')->unsigned();
            $table->foreign('jdd_jd_id', 'tbl_jdd_jd_id_foreign')->references('jd_id')->on('job_deposit');
            $table->bigInteger('jdd_jop_id')->unsigned()->nullable();
            $table->foreign('jdd_jop_id', 'tbl_jdd_jop_id_foreign')->references('jop_id')->on('job_purchase');
            $table->bigInteger('jdd_cc_id')->unsigned();
            $table->foreign('jdd_cc_id', 'tbl_jdd_cc_id')->references('cc_id')->on('cost_code');
            $table->string('jdd_description', 150);
            $table->float('jdd_rate');
            $table->float('jdd_quantity');
            $table->bigInteger('jdd_uom_id')->unsigned();
            $table->foreign('jdd_uom_id', 'tbl_jdd_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jdd_cur_id')->unsigned();
            $table->foreign('jdd_cur_id', 'tbl_jdd_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('jdd_exchange_rate');
            $table->bigInteger('jdd_tax_id')->unsigned()->nullable();
            $table->foreign('jdd_tax_id', 'tbl_jop_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('jdd_total');
            $table->bigInteger('jdd_created_by');
            $table->dateTime('jdd_created_on');
            $table->bigInteger('jdd_updated_by')->nullable();
            $table->dateTime('jdd_updated_on')->nullable();
            $table->bigInteger('jdd_deleted_by')->nullable();
            $table->dateTime('jdd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_deposit_detail');
    }
}
