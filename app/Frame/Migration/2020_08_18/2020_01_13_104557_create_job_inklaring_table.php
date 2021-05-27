<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInklaringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inklaring', function (Blueprint $table) {
            $table->bigIncrements('jik_id');
            $table->bigInteger('jik_jo_id')->unsigned();
            $table->foreign('jik_jo_id', 'tbl_jik_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jik_wh_id')->unsigned()->nullable();
            $table->foreign('jik_wh_id', 'tbl_jik_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->bigInteger('jik_cct_id')->unsigned()->nullable();
            $table->foreign('jik_cct_id', 'tbl_jik_cct_id_foreign')->references('cct_id')->on('customs_clearance_type');
            $table->bigInteger('jik_cdt_id')->unsigned();
            $table->foreign('jik_cdt_id', 'tbl_jik_cdt_id_foreign')->references('cdt_id')->on('customs_document_type');
            $table->date('jik_planning_date');
            $table->bigInteger('jik_consignee_id')->unsigned()->nullable();
            $table->foreign('jik_consignee_id', 'tbl_jik_consignee_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jik_of_consignee_id')->unsigned()->nullable();
            $table->foreign('jik_of_consignee_id', 'tbl_jik_of_consignee_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jik_pic_consignee_id')->unsigned()->nullable();
            $table->foreign('jik_pic_consignee_id', 'tbl_jik_pic_consignee_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jik_shipper_id')->unsigned()->nullable();
            $table->foreign('jik_shipper_id', 'tbl_jik_shipper_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jik_of_shipper_id')->unsigned()->nullable();
            $table->foreign('jik_of_shipper_id', 'tbl_jik_of_shipper_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jik_pic_shipper_id')->unsigned()->nullable();
            $table->foreign('jik_pic_shipper_id', 'tbl_jik_pic_shipper_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jik_notify_id')->unsigned()->nullable();
            $table->foreign('jik_notify_id', 'tbl_jik_notify_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jik_of_notify_id')->unsigned()->nullable();
            $table->foreign('jik_of_notify_id', 'tbl_jik_of_notify_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jik_pic_notify_id')->unsigned()->nullable();
            $table->foreign('jik_pic_notify_id', 'tbl_jik_pic_notify_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jik_pol_id')->unsigned()->nullable();
            $table->foreign('jik_pol_id', 'tbl_jik_pol_id_foreign')->references('po_id')->on('port');
            $table->bigInteger('jik_pod_id')->unsigned()->nullable();
            $table->foreign('jik_pod_id', 'tbl_jik_pod_id_foreign')->references('po_id')->on('port');
            $table->bigInteger('jik_tm_id')->unsigned()->nullable();
            $table->foreign('jik_tm_id', 'tbl_jik_tm_id_foreign')->references('tm_id')->on('transport_module');
            $table->string('jik_transport_name', 255)->nullable();
            $table->string('jik_voyage_number', 125)->nullable();
            $table->string('jik_register_number', 255)->nullable();
            $table->date('jik_register_date')->nullable();
            $table->string('jik_sppd_ref', 255)->nullable();
            $table->date('jik_sppd_date')->nullable();
            $table->string('jik_do_ref', 255)->nullable();
            $table->date('jik_do_expired')->nullable();
            $table->date('jik_eta_date')->nullable();
            $table->time('jik_eta_time')->nullable();
            $table->date('jik_ata_date')->nullable();
            $table->time('jik_ata_time')->nullable();
            $table->string('jik_manifest_ref', 255)->nullable();
            $table->date('jik_manifest_date')->nullable();
            $table->string('jik_manifest_pos', 255)->nullable();
            $table->string('jik_manifest_sub_pos', 255)->nullable();
            $table->dateTime('jik_drafting_on')->nullable();
            $table->bigInteger('jik_drafting_by')->nullable();
            $table->dateTime('jik_approve_on')->nullable();
            $table->bigInteger('jik_approve_by')->nullable();
            $table->dateTime('jik_register_on')->nullable();
            $table->bigInteger('jik_register_by')->nullable();
            $table->dateTime('jik_approve_pabean_on')->nullable();
            $table->bigInteger('jik_approve_pabean_by')->nullable();
            $table->dateTime('jik_port_release_on')->nullable();
            $table->bigInteger('jik_port_release_by')->nullable();
            $table->dateTime('jik_port_complete_on')->nullable();
            $table->bigInteger('jik_port_complete_by')->nullable();
            $table->dateTime('jik_release_on')->nullable();
            $table->bigInteger('jik_release_by')->nullable();
            $table->dateTime('jik_complete_release_on')->nullable();
            $table->bigInteger('jik_complete_release_by')->nullable();
            $table->dateTime('jik_gate_in_on')->nullable();
            $table->bigInteger('jik_gate_in_by')->nullable();
            $table->dateTime('jik_complete_gate_in_on')->nullable();
            $table->bigInteger('jik_complete_gate_in_by')->nullable();
            $table->bigInteger('jik_created_by');
            $table->dateTime('jik_created_on');
            $table->bigInteger('jik_updated_by')->nullable();
            $table->dateTime('jik_updated_on')->nullable();
            $table->bigInteger('jik_deleted_by')->nullable();
            $table->dateTime('jik_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inklaring');
    }
}
