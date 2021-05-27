<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price', function (Blueprint $table) {
            $table->bigIncrements('prc_id');
            $table->bigInteger('prc_ss_id')->unsigned();
            $table->foreign('prc_ss_id', 'tbl_prc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('prc_number', 128);
            $table->string('prc_code', 128)->nullable();
            $table->char('prc_type', 1)->default('Y');
            $table->bigInteger('prc_order_of_id')->unsigned()->nullable();
            $table->foreign('prc_order_of_id', 'tbl_prc_order_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('prc_inv_of_id')->unsigned()->nullable();
            $table->foreign('prc_inv_of_id', 'tbl_prc_inv_of_id_foreign')->references('of_id')->on('office');

            $table->bigInteger('prc_rel_id')->unsigned();
            $table->foreign('prc_rel_id', 'tbl_prc_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('prc_cp_id')->unsigned()->nullable();
            $table->foreign('prc_cp_id', 'tbl_prc_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('prc_srv_id')->unsigned();
            $table->foreign('prc_srv_id', 'tbl_prc_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('prc_srt_id')->unsigned();
            $table->foreign('prc_srt_id', 'tbl_prc_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->bigInteger('prc_us_id')->unsigned();
            $table->foreign('prc_us_id', 'tbl_prc_us_id_foreign')->references('us_id')->on('users');

            $table->date('prc_start_on');
            $table->date('prc_end_on');

            $table->float('prc_lead_time')->nullable();
            $table->bigInteger('prc_ct_id')->unsigned()->nullable();
            $table->foreign('prc_ct_id', 'tbl_prc_ct_id_foreign')->references('ct_id')->on('container');
            $table->bigInteger('prc_eg_id')->unsigned()->nullable();
            $table->foreign('prc_eg_id', 'tbl_prc_eg_id_foreign')->references('eg_id')->on('equipment_group');
            $table->bigInteger('prc_dtc_origin')->unsigned()->nullable();
            $table->foreign('prc_dtc_origin', 'tbl_prc_dtc_origin_foreign')->references('dtc_id')->on('district');
            $table->bigInteger('prc_dtc_destination')->unsigned()->nullable();
            $table->foreign('prc_dtc_destination', 'tbl_prc_dtc_destination_foreign')->references('dtc_id')->on('district');
            $table->bigInteger('prc_wh_id')->unsigned()->nullable();
            $table->foreign('prc_wh_id', 'tbl_prc_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('prc_tm_id')->unsigned()->nullable();
            $table->foreign('prc_tm_id', 'tbl_prc_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->bigInteger('prc_cct_id')->unsigned()->nullable();
            $table->foreign('prc_cct_id', 'tbl_prc_cct_id_foreign')->references('cct_id')->on('customs_clearance_type');
            $table->bigInteger('prc_po_origin')->unsigned()->nullable();
            $table->foreign('prc_po_origin', 'tbl_prc_po_origin_foreign')->references('po_id')->on('port');
            $table->bigInteger('prc_po_destination')->unsigned()->nullable();
            $table->foreign('prc_po_destination', 'tbl_prc_po_destination_foreign')->references('po_id')->on('port');

            $table->bigInteger('prc_approve_by')->unsigned()->nullable();
            $table->foreign('prc_approve_by', 'tbl_prc_approve_by_foreign')->references('us_id')->on('users');
            $table->dateTime('prc_approve_on')->nullable();

            $table->bigInteger('prc_created_by');
            $table->dateTime('prc_created_on');
            $table->bigInteger('prc_updated_by')->nullable();
            $table->dateTime('prc_updated_on')->nullable();
            $table->bigInteger('prc_deleted_by')->nullable();
            $table->dateTime('prc_deleted_on')->nullable();
            $table->string('prc_deleted_reason', 256)->nullable();
            $table->uuid('prc_uid');
            $table->unique('prc_uid', 'tbl_prc_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price');
    }
}
