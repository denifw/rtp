<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_archive', function (Blueprint $table) {
            $table->uuid('joa_id')->primary();
            $table->uuid('joa_jo_id')->unsigned();
            $table->foreign('joa_jo_id', 'tbl_joa_jo_id_fkey')->references('jo_id')->on('job_order');
            $table->uuid('joa_created_by');
            $table->dateTime('joa_created_on');
            $table->uuid('joa_updated_by')->nullable();
            $table->dateTime('joa_updated_on')->nullable();
            $table->uuid('joa_deleted_by')->nullable();
            $table->dateTime('joa_deleted_on')->nullable();
            $table->string('joa_deleted_reason', 256)->nullable();
        });
        Schema::table('job_order', function (Blueprint $table) {
            $table->uuid('jo_joa_id')->unsigned()->nullable();
            $table->foreign('jo_joa_id', 'tbl_jo_joa_id_fkey')->references('joa_id')->on('job_archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_order', function (Blueprint $table) {
            $table->dropForeign('tbl_jo_joa_id_fkey');
            $table->dropColumn('jo_joa_id');
        });
        Schema::dropIfExists('job_archive');
    }
}
