<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_request', function (Blueprint $table) {
            $table->bigIncrements('qtnr_id');
            $table->bigInteger('qtnr_qtn_id')->unsigned();
            $table->foreign('qtnr_qtn_id', 'tbl_qtnr_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnr_requested_by')->unsigned();
            $table->foreign('qtnr_requested_by', 'tbl_qtnr_requested_by_foreign')->references('us_id')->on('users');
            $table->string('qtnr_reject_reason', 255)->nullable();
            $table->bigInteger('qtnr_created_by');
            $table->dateTime('qtnr_created_on');
            $table->bigInteger('qtnr_updated_by')->nullable();
            $table->dateTime('qtnr_updated_on')->nullable();
            $table->bigInteger('qtnr_deleted_by')->nullable();
            $table->dateTime('qtnr_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_request');
    }
}
