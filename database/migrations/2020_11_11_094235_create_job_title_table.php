<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTitleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_title', function (Blueprint $table) {
            $table->bigIncrements('jbt_id');
            $table->bigInteger('jbt_ss_id')->unsigned();
            $table->foreign('jbt_ss_id', 'tbl_jbt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('jbt_name', 256);
            $table->char('jbt_active', 1)->default('Y');
            $table->bigInteger('jbt_created_by');
            $table->dateTime('jbt_created_on');
            $table->bigInteger('jbt_updated_by')->nullable();
            $table->dateTime('jbt_updated_on')->nullable();
            $table->bigInteger('jbt_deleted_by')->nullable();
            $table->dateTime('jbt_deleted_on')->nullable();
            $table->string('jbt_deleted_reason', 256)->nullable();
            $table->unique(['jbt_ss_id', 'jbt_name'], 'tbl_jbt_ss_id_name_unique');
            $table->uuid('jbt_uid');
            $table->unique('jbt_uid', 'tbl_jbt_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_title');
    }
}
