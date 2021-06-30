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
            $table->uuid('pid_id')->primary();
            $table->uuid('pid_pi_id')->unsigned();
            $table->foreign('pid_pi_id', 'tbl_pid_pi_id_foreign')->references('pi_id')->on('purchase_invoice');
            $table->uuid('pid_jo_id')->unsigned()->nullable();
            $table->foreign('pid_jo_id', 'tbl_pid_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->uuid('pid_cc_id')->unsigned();
            $table->foreign('pid_cc_id', 'tbl_pid_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->string('pid_description', 256);
            $table->float('pid_quantity');
            $table->uuid('pid_uom_id')->unsigned();
            $table->foreign('pid_uom_id', 'tbl_pid_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('pid_rate');
            $table->uuid('pid_cur_id')->unsigned();
            $table->foreign('pid_cur_id', 'tbl_pid_cur_id_foreign')->references('cur_id')->on('currency');
            $table->float('pid_exchange_rate');
            $table->uuid('pid_tax_id')->unsigned();
            $table->foreign('pid_tax_id', 'tbl_pid_tax_id_foreign')->references('tax_id')->on('tax');
            $table->float('pid_total');
            $table->uuid('pid_created_by');
            $table->dateTime('pid_created_on');
            $table->uuid('pid_updated_by')->nullable();
            $table->dateTime('pid_updated_on')->nullable();
            $table->uuid('pid_deleted_by')->nullable();
            $table->dateTime('pid_deleted_on')->nullable();
            $table->string('pid_deleted_reason', 256)->nullable();
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
