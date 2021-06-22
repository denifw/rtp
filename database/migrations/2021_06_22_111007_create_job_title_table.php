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
            $table->uuid('jt_id')->primary();
            $table->uuid('jt_ss_id')->unsigned();
            $table->foreign('jt_ss_id', 'tbl_jt_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('jt_description', 256);
            $table->uuid('jt_created_by');
            $table->dateTime('jt_created_on');
            $table->uuid('jt_updated_by')->nullable();
            $table->dateTime('jt_updated_on')->nullable();
            $table->uuid('jt_deleted_by')->nullable();
            $table->dateTime('jt_deleted_on')->nullable();
            $table->string('jt_deleted_reason', 256)->nullable();
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
