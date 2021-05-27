<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_order', function (Blueprint $table) {
            $table->bigIncrements('rno_id');
            $table->bigInteger('rno_ss_id')->unsigned();
            $table->foreign('rno_ss_id', 'tbl_rno_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('rno_number', 255);
            $table->bigInteger('rno_eq_id')->unsigned();
            $table->foreign('rno_eq_id', 'tbl_rno_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->date('rno_order_date');
            $table->date('rno_planning_date');
            $table->bigInteger('rno_vendor_id')->unsigned();
            $table->foreign('rno_vendor_id', 'tbl_rno_vendor_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('rno_manager_id')->unsigned();
            $table->foreign('rno_manager_id', 'tbl_rno_manager_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('rno_request_by_id')->unsigned();
            $table->foreign('rno_request_by_id', 'tbl_rno_request_by_id_foreign')->references('us_id')->on('users');
            $table->string('rno_remark', 255)->nullable();
            $table->string('rno_deleted_reason', 255)->nullable();
            $table->bigInteger('rno_approved_by')->nullable();
            $table->dateTime('rno_approved_on')->nullable();
            $table->date('rno_start_renewal_date')->nullable();
            $table->time('rno_start_renewal_time')->nullable();
            $table->bigInteger('rno_start_renewal_by')->nullable();
            $table->bigInteger('rno_finish_by')->nullable();
            $table->dateTime('rno_finish_on')->nullable();
            $table->bigInteger('rno_created_by');
            $table->dateTime('rno_created_on');
            $table->bigInteger('rno_updated_by')->nullable();
            $table->dateTime('rno_updated_on')->nullable();
            $table->bigInteger('rno_deleted_by')->nullable();
            $table->dateTime('rno_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_order');
    }
}
