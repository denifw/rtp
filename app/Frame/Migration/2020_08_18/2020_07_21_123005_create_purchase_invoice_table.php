<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice', function (Blueprint $table) {
            $table->bigIncrements('pi_id');
            $table->bigInteger('pi_ss_id')->unsigned();
            $table->foreign('pi_ss_id', 'tbl_pi_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('pi_number',  255)->nullable();
            $table->string('pi_reference',  255)->nullable();
            $table->bigInteger('pi_srv_id')->unsigned();
            $table->foreign('pi_srv_id', 'tbl_pi_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('pi_of_id')->unsigned()->nullable();
            $table->foreign('pi_of_id', 'tbl_pi_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('pi_rel_id')->unsigned();
            $table->foreign('pi_rel_id', 'tbl_pi_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('pi_rb_id')->unsigned()->nullable();
            $table->foreign('pi_rb_id', 'tbl_pi_rb_id_foreign')->references('rb_id')->on('relation_bank');
            $table->bigInteger('pi_rel_of_id')->unsigned()->nullable();
            $table->foreign('pi_rel_of_id', 'tbl_pi_rel_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('pi_cp_id')->unsigned()->nullable();
            $table->foreign('pi_cp_id', 'tbl_pi_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('pi_rel_reference',  255)->nullable();
            $table->bigInteger('pi_doc_id')->unsigned()->nullable();
            $table->foreign('pi_doc_id', 'tbl_pi_doc_id_id_foreign')->references('doc_id')->on('document');
            $table->bigInteger('pi_doc_tax_id')->unsigned()->nullable();
            $table->foreign('pi_doc_tax_id', 'tbl_pi_pi_doc_tax_id_foreign')->references('doc_id')->on('document');
            $table->date('pi_date');
            $table->date('pi_due_date');

            $table->bigInteger('pi_approve_by')->unsigned()->nullable();
            $table->foreign('pi_approve_by', 'tbl_pi_approve_by_foreign')->references('us_id')->on('users');
            $table->dateTime('pi_approve_on')->nullable();

            $table->date('pi_pay_date')->nullable();
            $table->string('pi_paid_ref',  255)->nullable();
            $table->bigInteger('pi_paid_by')->unsigned()->nullable();
            $table->foreign('pi_paid_by', 'tbl_pi_paid_by_foreign')->references('us_id')->on('users');
            $table->dateTime('pi_paid_on')->nullable();
            $table->bigInteger('pi_paid_rb_id')->unsigned()->nullable();
            $table->foreign('pi_paid_rb_id', 'tbl_pi_paid_rb_id_foreign')->references('rb_id')->on('relation_bank');
            $table->bigInteger('pi_ca_id')->unsigned()->nullable();
            $table->foreign('pi_ca_id', 'tbl_pi_ca_id_foreign')->references('ca_id')->on('cash_advance');

            $table->bigInteger('pi_created_by');
            $table->dateTime('pi_created_on');
            $table->bigInteger('pi_updated_by')->nullable();
            $table->dateTime('pi_updated_on')->nullable();
            $table->string('pi_deleted_reason', 255)->nullable();
            $table->bigInteger('pi_deleted_by')->nullable();
            $table->dateTime('pi_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice');
    }
}
