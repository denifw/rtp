<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation', function (Blueprint $table) {
            $table->bigIncrements('qtn_id');
            $table->bigInteger('qtn_ss_id')->unsigned();
            $table->foreign('qtn_ss_id', 'tbl_qtn_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('qtn_number', 50);
            $table->char('qtn_type', 1);
            $table->bigInteger('qtn_rel_id')->unsigned();
            $table->foreign('qtn_rel_id', 'tbl_qtn_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('qtn_pic_id')->unsigned()->nullable();
            $table->foreign('qtn_pic_id', 'tbl_qtn_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('qtn_order_of_id')->unsigned();
            $table->foreign('qtn_order_of_id', 'tbl_qtn_order_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('qtn_invoice_of_id')->unsigned();
            $table->foreign('qtn_invoice_of_id', 'tbl_qtn_invoice_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('qtn_srv_id')->unsigned();
            $table->foreign('qtn_srv_id', 'tbl_qtn_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('qtn_srt_id')->unsigned();
            $table->foreign('qtn_srt_id', 'tbl_qtn_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->bigInteger('qtn_manager_id')->unsigned()->nullable();
            $table->foreign('qtn_manager_id', 'tbl_qtn_manager_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('qtn_ct_id')->unsigned()->nullable();
            $table->foreign('qtn_ct_id', 'tbl_qtn_ct_id_foreign')->references('ct_id')->on('container');
            $table->float('qtn_lead_time')->nullable();
            $table->date('qtn_start_date');
            $table->date('qtn_end_date');
            $table->bigInteger('qtn_approved_by')->unsigned()->nullable();
            $table->foreign('qtn_approved_by', 'tbl_qtn_approved_by_foreign')->references('us_id')->on('users');
            $table->dateTime('qtn_approved_on')->nullable();
            $table->char('qtn_active', 1)->default('Y');
            $table->bigInteger('qtn_created_by');
            $table->dateTime('qtn_created_on');
            $table->bigInteger('qtn_updated_by')->nullable();
            $table->dateTime('qtn_updated_on')->nullable();
            $table->bigInteger('qtn_deleted_by')->nullable();
            $table->dateTime('qtn_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation');
    }
}
