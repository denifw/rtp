<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderIssueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_issue', function (Blueprint $table) {
            $table->bigIncrements('soi_id');
            $table->bigInteger('soi_ss_id')->unsigned();
            $table->foreign('soi_ss_id', 'tbl_soi_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('soi_number', 256);
            $table->bigInteger('soi_rel_id')->unsigned();
            $table->foreign('soi_rel_id', 'tbl_soi_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('soi_pic_id')->unsigned()->nullable();
            $table->foreign('soi_pic_id', 'tbl_soi_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('soi_srv_id')->unsigned();
            $table->foreign('soi_srv_id', 'tbl_soi_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('soi_so_id')->unsigned();
            $table->foreign('soi_so_id', 'tbl_soi_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('soi_jo_id')->unsigned()->nullable();
            $table->foreign('soi_jo_id', 'tbl_soi_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->string('soi_subject', 256);
            $table->date('soi_report_date');
            $table->bigInteger('soi_assign_id')->unsigned();
            $table->foreign('soi_assign_id', 'tbl_soi_assign_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('soi_priority_id')->unsigned();
            $table->foreign('soi_priority_id', 'tbl_soi_priority_id_foreign')->references('sty_id')->on('system_type');
            $table->bigInteger('soi_pic_field_id')->unsigned()->nullable();
            $table->foreign('soi_pic_field_id', 'tbl_soi_pic_field_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('soi_description', 256);
            $table->string('soi_solution', 256)->nullable();
            $table->string('soi_note', 256)->nullable();
            $table->bigInteger('soi_finish_by')->nullable();
            $table->foreign('soi_finish_by', 'tbl_soi_finish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('soi_finish_on')->nullable();
            $table->bigInteger('soi_created_by');
            $table->dateTime('soi_created_on');
            $table->bigInteger('soi_updated_by')->nullable();
            $table->dateTime('soi_updated_on')->nullable();
            $table->bigInteger('soi_deleted_by')->nullable();
            $table->dateTime('soi_deleted_on')->nullable();
            $table->string('soi_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_issue');
    }
}
