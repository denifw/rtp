<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobFinanceAddPriceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_sales', function (Blueprint $table) {
            $table->bigInteger('jos_prd_id')->unsigned()->nullable();
            $table->foreign('jos_prd_id', 'tbl_jos_prd_id_fkey')->references('prd_id')->on('price_detail');
            $table->float('jos_exchange_rate')->nullable(true)->change();
            $table->bigInteger('jos_sid_id')->unsigned()->nullable();
            $table->foreign('jos_sid_id', 'tbl_jos_sid_id_fkey')->references('sid_id')->on('sales_invoice_detail');
        });
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->bigInteger('jop_prd_id')->unsigned()->nullable();
            $table->foreign('jop_prd_id', 'tbl_jop_prd_id_fkey')->references('prd_id')->on('price_detail');
            $table->float('jop_exchange_rate')->nullable(true)->change();
            $table->bigInteger('jop_pid_id')->unsigned()->nullable();
            $table->foreign('jop_pid_id', 'tbl_jop_pid_id_fkey')->references('pid_id')->on('purchase_invoice_detail');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
