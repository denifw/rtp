<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationForwardingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_forwarding', function (Blueprint $table) {
            $table->bigIncrements('qtnf_id');
            $table->bigInteger('qtnf_qtn_id')->unsigned();
            $table->foreign('qtnf_qtn_id', 'tbl_qtnf_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnf_po_origin')->unsigned();
            $table->foreign('qtnf_po_origin', 'tbl_qtnf_po_origin_foreign')->references('po_id')->on('port');
            $table->bigInteger('qtnf_po_destination')->unsigned();
            $table->foreign('qtnf_po_destination', 'tbl_qtnf_po_destination_foreign')->references('po_id')->on('port');
            $table->bigInteger('qtnf_created_by');
            $table->dateTime('qtnf_created_on');
            $table->bigInteger('qtnf_updated_by')->nullable();
            $table->dateTime('qtnf_updated_on')->nullable();
            $table->bigInteger('qtnf_deleted_by')->nullable();
            $table->dateTime('qtnf_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_forwarding');
    }
}
