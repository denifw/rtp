<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice_detail', function (Blueprint $table) {
            $table->bigIncrements('pid_id');
            $table->bigInteger('pid_pi_id')->unsigned();
            $table->foreign('pid_pi_id', 'tbl_pid_pi_id_foreign')->references('pi_id')->on('purchase_invoice');
            $table->bigInteger('pid_jop_id')->unsigned()->nullable();
            $table->foreign('pid_jop_id', 'tbl_pid_jop_id_foreign')->references('jop_id')->on('job_purchase');
            $table->bigInteger('pid_cc_id')->unsigned()->nullable();
            $table->foreign('pid_cc_id', 'tbl_pid_cc_id')->references('cc_id')->on('cost_code');
            $table->string('pid_description', 150)->nullable();
            $table->float('pid_rate')->nullable();
            $table->float('pid_minimum_rate')->nullable();
            $table->float('pid_quantity')->nullable();
            $table->bigInteger('pid_uom_id')->unsigned()->nullable();
            $table->foreign('pid_uom_id', 'tbl_pid_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('pid_cur_id')->unsigned()->nullable();
            $table->foreign('pid_cur_id', 'tbl_pid_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('pid_exchange_rate')->nullable();
            $table->bigInteger('pid_tax_id')->unsigned()->nullable();
            $table->foreign('pid_tax_id', 'tbl_pid_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('pid_total')->nullable();
            $table->bigInteger('pid_created_by');
            $table->dateTime('pid_created_on');
            $table->bigInteger('pid_updated_by')->nullable();
            $table->dateTime('pid_updated_on')->nullable();
            $table->bigInteger('pid_deleted_by')->nullable();
            $table->dateTime('pid_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_detail');
    }
}
