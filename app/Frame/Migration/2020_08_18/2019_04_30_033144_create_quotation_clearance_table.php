<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationClearanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_clearance', function (Blueprint $table) {
            $table->bigIncrements('qtnc_id');
            $table->bigInteger('qtnc_qtn_id')->unsigned();
            $table->foreign('qtnc_qtn_id', 'tbl_qtnc_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnc_tm_id')->unsigned();
            $table->foreign('qtnc_tm_id', 'tbl_qtnc_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->bigInteger('qtnc_po_id')->unsigned();
            $table->foreign('qtnc_po_id', 'tbl_qtnc_po_id_foreign')->references('po_id')->on('port');
            $table->bigInteger('qtnc_created_by');
            $table->dateTime('qtnc_created_on');
            $table->bigInteger('qtnc_updated_by')->nullable();
            $table->dateTime('qtnc_updated_on')->nullable();
            $table->bigInteger('qtnc_deleted_by')->nullable();
            $table->dateTime('qtnc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_clearance');
    }
}
