<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_order', function (Blueprint $table) {
            $table->bigIncrements('jo_id');
            $table->bigInteger('jo_ss_id')->unsigned();
            $table->foreign('jo_ss_id', 'tbl_jo_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('jo_ref_id')->unsigned()->nullable();
            $table->string('jo_number', 255);
            $table->bigInteger('jo_srv_id')->unsigned();
            $table->foreign('jo_srv_id', 'tbl_jo_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('jo_srt_id')->unsigned();
            $table->foreign('jo_srt_id', 'tbl_jo_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->date('jo_order_date');
            $table->bigInteger('jo_rel_id')->unsigned()->nullable();
            $table->foreign('jo_rel_id', 'tbl_jo_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('jo_customer_ref', 255)->nullable();
            $table->bigInteger('jo_pic_id')->unsigned()->nullable();
            $table->foreign('jo_pic_id', 'tbl_jo_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jo_order_of_id')->unsigned();
            $table->foreign('jo_order_of_id', 'tbl_jo_order_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jo_invoice_of_id')->unsigned()->nullable();
            $table->foreign('jo_invoice_of_id', 'tbl_jo_invoice_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jo_manager_id')->unsigned();
            $table->foreign('jo_manager_id', 'tbl_jo_manager_id_foreign')->references('us_id')->on('users');
            $table->string('jo_contract_ref', 255)->nullable();
            $table->string('jo_bl_ref', 255)->nullable();
            $table->string('jo_sppb_ref', 255)->nullable();
            $table->string('jo_packing_ref', 255)->nullable();
            $table->bigInteger('jo_publish_by')->unsigned()->nullable();
            $table->foreign('jo_publish_by', 'tbl_jo_publish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jo_publish_on')->nullable();
            $table->bigInteger('jo_start_by')->unsigned()->nullable();
            $table->foreign('jo_start_by', 'tbl_jo_start_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jo_start_on')->nullable();
            $table->bigInteger('jo_document_by')->nullable();
            $table->foreign('jo_document_by', 'tbl_jo_document_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jo_document_on')->nullable();
            $table->bigInteger('jo_finish_by')->unsigned()->nullable();
            $table->foreign('jo_finish_by', 'tbl_jo_finish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jo_finish_on')->nullable();
            $table->string('jo_deleted_reason', 255)->nullable();
            $table->char('jo_active', 1)->default('Y');
            $table->bigInteger('jo_created_by');
            $table->dateTime('jo_created_on');
            $table->bigInteger('jo_updated_by')->nullable();
            $table->dateTime('jo_updated_on')->nullable();
            $table->bigInteger('jo_deleted_by')->nullable();
            $table->dateTime('jo_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_order');
    }
}
