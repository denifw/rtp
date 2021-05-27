<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDatabaseAvoidLateral extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_order', function (Blueprint $table) {
            $table->bigInteger('jo_jae_id')->unsigned()->nullable();
            $table->foreign('jo_jae_id', 'tbl_jo_jae_id_foreign')->references('jae_id')->on('job_action_event');
            $table->bigInteger('jo_joh_id')->unsigned()->nullable();
            $table->foreign('jo_joh_id', 'tbl_jo_joh_id_foreign')->references('joh_id')->on('job_order_hold');
            $table->bigInteger('jo_joa_id')->unsigned()->nullable();
            $table->foreign('jo_joa_id', 'tbl_jo_joa_id_foreign')->references('joa_id')->on('job_order_archive');
        });
        Schema::table('job_action_event', function (Blueprint $table) {
            $table->bigInteger('jae_doc_id')->unsigned()->nullable();
            $table->foreign('jae_doc_id', 'tbl_jae_doc_id_foreign')->references('doc_id')->on('document');
        });
        Schema::table('job_purchase', function (Blueprint $table) {
            $table->bigInteger('jop_doc_id')->unsigned()->nullable();
            $table->foreign('jop_doc_id', 'tbl_jop_doc_id_foreign')->references('doc_id')->on('document');
        });
        Schema::table('sales_order', function (Blueprint $table) {
            $table->bigInteger('so_start_by')->unsigned()->nullable();
            $table->foreign('so_start_by', 'tbl_so_start_by_foreign')->references('us_id')->on('users');
            $table->dateTime('so_start_on')->nullable();
            $table->bigInteger('so_soh_id')->unsigned()->nullable();
            $table->foreign('so_soh_id', 'tbl_so_soh_id_foreign')->references('soh_id')->on('sales_order_hold');
            $table->bigInteger('so_soa_id')->unsigned()->nullable();
            $table->foreign('so_soa_id', 'tbl_so_soa_id_foreign')->references('soa_id')->on('sales_order_archive');
        });
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->bigInteger('ptc_pcr_id')->unsigned()->nullable();
            $table->foreign('ptc_pcr_id', 'tbl_ptc_pcr_id_foreign')->references('pcr_id')->on('petty_cash_request');
        });
        Schema::table('job_deposit', function (Blueprint $table) {
            $table->bigInteger('jd_jda_id')->unsigned()->nullable();
            $table->foreign('jd_jda_id', 'tbl_jd_jda_id_foreign')->references('jda_id')->on('job_deposit_approval');
        });
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->bigInteger('pi_pia_id')->unsigned()->nullable();
            $table->foreign('pi_pia_id', 'tbl_pi_pia_id_foreign')->references('pia_id')->on('purchase_invoice_approval');
        });
        Schema::table('sales_invoice', function (Blueprint $table) {
            $table->bigInteger('si_sia_id')->unsigned()->nullable();
            $table->foreign('si_sia_id', 'tbl_si_sia_id_foreign')->references('sia_id')->on('sales_invoice_approval');
        });
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->bigInteger('ca_carc_id')->unsigned()->nullable();
            $table->foreign('ca_carc_id', 'tbl_ca_carc_id_foreign')->references('carc_id')->on('cash_advance_received');
            $table->bigInteger('ca_cart_id')->unsigned()->nullable();
            $table->foreign('ca_cart_id', 'tbl_ca_cart_id_foreign')->references('cart_id')->on('cash_advance_returned');
        });
        Schema::table('service_order', function (Blueprint $table) {
            $table->bigInteger('svo_svr_id')->unsigned()->nullable();
            $table->foreign('svo_svr_id', 'tbl_svo_svr_id_foreign')->references('svr_id')->on('service_order_request');
        });
        Schema::table('renewal_order', function (Blueprint $table) {
            $table->bigInteger('rno_rnr_id')->unsigned()->nullable();
            $table->foreign('rno_rnr_id', 'tbl_rno_rnr_id_foreign')->references('rnr_id')->on('renewal_order_request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
