<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDepositTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_deposit', function (Blueprint $table) {
            $table->bigIncrements('jd_id');
            $table->string('jd_number')->nullable();
            $table->bigInteger('jd_ss_id')->unsigned();
            $table->foreign('jd_ss_id', 'tbl_jd_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('jd_jo_id')->unsigned();
            $table->foreign('jd_jo_id', 'tbl_jd_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jd_rel_id')->unsigned();
            $table->foreign('jd_rel_id', 'tbl_jd_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('jd_of_id')->unsigned();
            $table->foreign('jd_of_id', 'tbl_jd_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('jd_cp_id')->unsigned()->nullable();
            $table->foreign('jd_cp_id', 'tbl_jd_cp_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('jd_rb_rel')->unsigned()->nullable();
            $table->foreign('jd_rb_rel', 'tbl_jd_rb_rel_foreign')->references('rb_id')->on('relation_bank');
            $table->bigInteger('jd_cc_id')->unsigned();
            $table->foreign('jd_cc_id', 'tbl_jd_cc_id_foreign')->references('cc_id')->on('cost_code');
            $table->bigInteger('jd_rb_paid')->unsigned()->nullable();
            $table->foreign('jd_rb_paid', 'tbl_jd_rb_paid_foreign')->references('rb_id')->on('relation_bank');
            $table->string('jd_rel_ref')->nullable();
            $table->string('jd_paid_ref')->nullable();
            $table->date('jd_date');
            $table->date('jd_return_date');
            $table->float('jd_amount');
            $table->string('jd_settle_ref')->nullable();
            $table->bigInteger('jd_rb_return')->unsigned()->nullable();
            $table->foreign('jd_rb_return', 'tbl_jd_rb_return_foreign')->references('rb_id')->on('relation_bank');
            $table->dateTime('jd_approved_on')->nullable();
            $table->bigInteger('jd_approved_by')->unsigned()->nullable();
            $table->foreign('jd_approved_by', 'tbl_jd_approved_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jd_paid_on')->nullable();
            $table->bigInteger('jd_paid_by')->unsigned()->nullable();
            $table->foreign('jd_paid_by', 'tbl_jd_paid_by_foreign')->references('us_id')->on('users');
            $table->bigInteger('jd_pm_id')->unsigned()->nullable();
            $table->foreign('jd_pm_id', 'tbl_jd_pm_id_foreign')->references('pm_id')->on('payment_method');
            $table->dateTime('jd_settle_on')->nullable();
            $table->bigInteger('jd_settle_by')->unsigned()->nullable();
            $table->foreign('jd_settle_by', 'tbl_jd_settle_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jd_return_on')->nullable();
            $table->bigInteger('jd_return_by')->unsigned()->nullable();
            $table->foreign('jd_return_by', 'tbl_jd_return_by_foreign')->references('us_id')->on('users');
            $table->bigInteger('jd_created_by');
            $table->dateTime('jd_created_on');
            $table->bigInteger('jd_updated_by')->nullable();
            $table->dateTime('jd_updated_on')->nullable();
            $table->string('jd_deleted_reason', 255)->nullable();
            $table->bigInteger('jd_deleted_by')->nullable();
            $table->dateTime('jd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_deposit');
    }
}
