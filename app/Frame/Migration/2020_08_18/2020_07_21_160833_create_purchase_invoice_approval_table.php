<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoiceApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice_approval', function (Blueprint $table) {
            $table->bigIncrements('pia_id');
            $table->bigInteger('pia_pi_id')->unsigned();
            $table->foreign('pia_pi_id', 'tbl_pia_pi_id_foreign')->references('pi_id')->on('purchase_invoice');
            $table->string('pia_reject_reason', 255)->nullable();
            $table->bigInteger('pia_created_by');
            $table->dateTime('pia_created_on');
            $table->bigInteger('pia_updated_by')->nullable();
            $table->dateTime('pia_updated_on')->nullable();
            $table->bigInteger('pia_deleted_by')->nullable();
            $table->dateTime('pia_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_approval');
    }
}
