<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobActionEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_action_event', function (Blueprint $table) {
            $table->bigIncrements('jae_id');
            $table->string('jae_description', 255);
            $table->bigInteger('jae_jac_id')->unsigned();
            $table->foreign('jae_jac_id', 'tbl_jae_jac_id_foreign')->references('jac_id')->on('job_action');
            $table->bigInteger('jae_sae_id')->unsigned()->nullable();
            $table->foreign('jae_sae_id', 'tbl_jae_sae_id_foreign')->references('sae_id')->on('system_action_event');
            $table->string('jae_remark', 255)->nullable();
            $table->date('jae_date')->nullable();
            $table->time('jae_time')->nullable();
            $table->char('jae_active', 1)->default('Y');
            $table->bigInteger('jae_created_by');
            $table->dateTime('jae_created_on');
            $table->bigInteger('jae_updated_by')->nullable();
            $table->dateTime('jae_updated_on')->nullable();
            $table->bigInteger('jae_deleted_by')->nullable();
            $table->dateTime('jae_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_action_event');
    }
}
