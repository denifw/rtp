<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobDepositApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_deposit_approval', function (Blueprint $table) {
            $table->bigIncrements('jda_id');
            $table->bigInteger('jda_jd_id')->unsigned();
            $table->foreign('jda_jd_id', 'tbl_jda_jd_id_foreign')->references('jd_id')->on('job_deposit');
            $table->string('jda_reject_reason', 255)->nullable();
            $table->bigInteger('jda_created_by');
            $table->dateTime('jda_created_on');
            $table->bigInteger('jda_updated_by')->nullable();
            $table->dateTime('jda_updated_on')->nullable();
            $table->bigInteger('jda_deleted_by')->nullable();
            $table->dateTime('jda_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_deposit_approval');
    }
}
