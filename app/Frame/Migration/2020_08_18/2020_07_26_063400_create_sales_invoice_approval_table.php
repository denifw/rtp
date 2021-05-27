<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoiceApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice_approval', function (Blueprint $table) {
            $table->bigIncrements('sia_id');
            $table->bigInteger('sia_si_id')->unsigned();
            $table->foreign('sia_si_id', 'tbl_sia_si_id_foreign')->references('si_id')->on('sales_invoice');
            $table->string('sia_reject_reason', 255)->nullable();
            $table->bigInteger('sia_created_by');
            $table->dateTime('sia_created_on');
            $table->bigInteger('sia_updated_by')->nullable();
            $table->dateTime('sia_updated_on')->nullable();
            $table->bigInteger('sia_deleted_by')->nullable();
            $table->dateTime('sia_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice_approval');
    }
}
