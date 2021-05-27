<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice', function (Blueprint $table) {
            $table->bigIncrements('si_id');
            $table->bigInteger('si_ss_id')->unsigned();
            $table->foreign('si_ss_id', 'tbl_si_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('si_number', 255)->nullable();
            $table->char('si_manual', 1)->default('N');
            $table->bigInteger('si_of_id')->unsigned();
            $table->foreign('si_of_id', 'tbl_si_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('si_rb_id')->unsigned();
            $table->foreign('si_rb_id', 'tbl_si_rb_id_foreign')->references('rb_id')->on('relation_bank');
            $table->bigInteger('si_rel_id')->unsigned();
            $table->foreign('si_rel_id', 'tbl_si_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('si_so_id')->unsigned()->nullable();
            $table->foreign('si_so_id', 'tbl_si_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('si_rel_of_id')->unsigned()->nullable();
            $table->foreign('si_rel_of_id', 'tbl_si_rel_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('si_cp_id')->unsigned()->nullable();
            $table->foreign('si_cp_id', 'tbl_si_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('si_rel_reference', 255)->nullable();

            $table->date('si_date')->nullable();
            $table->bigInteger('si_pt_id')->unsigned();
            $table->foreign('si_pt_id', 'tbl_si_pt_id_foreign')->references('pt_id')->on('payment_terms');
            $table->date('si_due_date')->nullable();

            $table->bigInteger('si_approve_by')->unsigned()->nullable();
            $table->foreign('si_approve_by', 'tbl_si_approve_by_foreign')->references('us_id')->on('users');
            $table->dateTime('si_approve_on')->nullable();

            $table->bigInteger('si_receive_id')->unsigned()->nullable();
            $table->foreign('si_receive_id', 'tbl_si_receive_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('si_receive_by')->unsigned()->nullable();
            $table->foreign('si_receive_by', 'tbl_si_receive_by_foreign')->references('us_id')->on('users');
            $table->dateTime('si_receive_on')->nullable();

            $table->datetime('si_pay_time')->nullable();
            $table->string('si_paid_ref', 255)->nullable();
            $table->bigInteger('si_paid_by')->unsigned()->nullable();
            $table->foreign('si_paid_by', 'tbl_si_paid_by_foreign')->references('us_id')->on('users');
            $table->dateTime('si_paid_on')->nullable();

            $table->bigInteger('si_created_by');
            $table->dateTime('si_created_on');
            $table->bigInteger('si_updated_by')->nullable();
            $table->dateTime('si_updated_on')->nullable();
            $table->string('si_deleted_reason', 255)->nullable();
            $table->bigInteger('si_deleted_by')->nullable();
            $table->dateTime('si_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice');
    }
}
