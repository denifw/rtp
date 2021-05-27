<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceReminderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_reminder', function (Blueprint $table) {
            $table->bigIncrements('svrm_id');
            $table->bigInteger('svrm_ss_id')->unsigned();
            $table->foreign('svrm_ss_id', 'tbl_svrm_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('svrm_eq_id')->unsigned();
            $table->foreign('svrm_eq_id', 'tbl_svrm_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->bigInteger('svrm_svt_id')->unsigned();
            $table->foreign('svrm_svt_id', 'tbl_svrm_svt_id_foreign')->references('svt_id')->on('service_task');
            $table->integer('svrm_meter_interval')->nullable();
            $table->integer('svrm_time_interval')->nullable();
            $table->string('svrm_time_interval_period')->nullable();
            $table->integer('svrm_meter_threshold')->nullable();
            $table->integer('svrm_time_threshold')->nullable();
            $table->string('svrm_time_threshold_period')->nullable();
            $table->date('svrm_next_due_date')->nullable();
            $table->date('svrm_next_due_date_threshold')->nullable();
            $table->string('svrm_remark', 255)->nullable();
            $table->bigInteger('svrm_created_by');
            $table->dateTime('svrm_created_on');
            $table->bigInteger('svrm_updated_by')->nullable();
            $table->dateTime('svrm_updated_on')->nullable();
            $table->bigInteger('svrm_deleted_by')->nullable();
            $table->dateTime('svrm_deleted_on')->nullable();
            $table->unique(['svrm_ss_id',  'svrm_eq_id', 'svrm_svt_id'], 'tbl_svrm_ss_id_eq_id_svt_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_reminder');
    }
}
