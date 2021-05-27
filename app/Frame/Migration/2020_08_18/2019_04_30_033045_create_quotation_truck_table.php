<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationTruckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_truck', function (Blueprint $table) {
            $table->bigIncrements('qtnt_id');
            $table->bigInteger('qtnt_qtn_id')->unsigned();
            $table->foreign('qtnt_qtn_id', 'tbl_qtnt_qtn_id_foreign')->references('qtn_id')->on('quotation');
            $table->bigInteger('qtnt_eg_id')->unsigned()->nullable();
            $table->foreign('qtnt_eg_id', 'tbl_qtnt_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->bigInteger('qtnt_cnt_origin')->unsigned();
            $table->foreign('qtnt_cnt_origin', 'tbl_qtnt_cnt_origin_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('qtnt_stt_origin')->unsigned();
            $table->foreign('qtnt_stt_origin', 'tbl_qtnt_stt_origin_foreign')->references('stt_id')->on('state');
            $table->bigInteger('qtnt_cty_origin')->unsigned();
            $table->foreign('qtnt_cty_origin', 'tbl_qtnt_cty_origin_foreign')->references('cty_id')->on('city');
            $table->bigInteger('qtnt_dtc_origin')->unsigned()->nullable();
            $table->foreign('qtnt_dtc_origin', 'tbl_qtnt_dtc_origin_foreign')->references('dtc_id')->on('district');
            $table->bigInteger('qtnt_cnt_destination')->unsigned();
            $table->foreign('qtnt_cnt_destination', 'tbl_qtnt_cnt_destination_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('qtnt_stt_destination')->unsigned();
            $table->foreign('qtnt_stt_destination', 'tbl_qtnt_stt_destination_foreign')->references('stt_id')->on('state');
            $table->bigInteger('qtnt_cty_destination')->unsigned();
            $table->foreign('qtnt_cty_destination', 'tbl_qtnt_cty_destination_foreign')->references('cty_id')->on('city');
            $table->bigInteger('qtnt_dtc_destination')->unsigned()->nullable();
            $table->foreign('qtnt_dtc_destination', 'tbl_qtnt_dtc_destination_foreign')->references('dtc_id')->on('district');
            $table->bigInteger('qtnt_created_by');
            $table->dateTime('qtnt_created_on');
            $table->bigInteger('qtnt_updated_by')->nullable();
            $table->dateTime('qtnt_updated_on')->nullable();
            $table->bigInteger('qtnt_deleted_by')->nullable();
            $table->dateTime('qtnt_deleted_on')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_truck');
    }
}
