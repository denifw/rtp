<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalReminderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewal_reminder', function (Blueprint $table) {
            $table->bigIncrements('rnrm_id');
            $table->bigInteger('rnrm_ss_id')->unsigned();
            $table->foreign('rnrm_ss_id', 'tbl_rnrm_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('rnrm_eq_id')->unsigned();
            $table->foreign('rnrm_eq_id', 'tbl_rnrm_eq_id_foreign')->references('eq_id')->on('equipment');
            $table->bigInteger('rnrm_rnt_id')->unsigned();
            $table->foreign('rnrm_rnt_id', 'tbl_rnrm_rnt_id_foreign')->references('rnt_id')->on('renewal_type');
            $table->integer('rnrm_interval');
            $table->string('rnrm_interval_period');
            $table->integer('rnrm_threshold');
            $table->string('rnrm_threshold_period');
            $table->date('rnrm_expiry_date');
            $table->date('rnrm_expiry_threshold_date');
            $table->string('rnrm_remark', 255)->nullable();
            $table->bigInteger('rnrm_created_by');
            $table->dateTime('rnrm_created_on');
            $table->bigInteger('rnrm_updated_by')->nullable();
            $table->dateTime('rnrm_updated_on')->nullable();
            $table->bigInteger('rnrm_deleted_by')->nullable();
            $table->dateTime('rnrm_deleted_on')->nullable();
            $table->unique(['rnrm_ss_id',  'rnrm_eq_id', 'rnrm_rnt_id'], 'tbl_rnrm_ss_id_eq_id_rnt_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_reminder');
    }
}
