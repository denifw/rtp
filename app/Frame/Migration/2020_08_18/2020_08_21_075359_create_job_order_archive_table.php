<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_order_archive', function (Blueprint $table) {
            $table->bigIncrements('joa_id');
            $table->bigInteger('joa_jo_id')->unsigned();
            $table->foreign('joa_jo_id', 'tbl_joa_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('joa_created_by');
            $table->dateTime('joa_created_on');
            $table->bigInteger('joa_updated_by')->nullable();
            $table->dateTime('joa_updated_on')->nullable();
            $table->string('joa_deleted_reason', 255)->nullable();
            $table->bigInteger('joa_deleted_by')->nullable();
            $table->dateTime('joa_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_order_archive');
    }
}
