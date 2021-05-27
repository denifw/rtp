<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobContainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_container', function (Blueprint $table) {
            $table->bigIncrements('joc_id');
            $table->bigInteger('joc_jo_id')->unsigned();
            $table->foreign('joc_jo_id', 'tbl_joc_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('joc_ct_id')->unsigned();
            $table->foreign('joc_ct_id', 'tbl_joc_ct_id_foreign')->references('ct_id')->on('container');
            $table->string('joc_container_number', 255);
            $table->string('joc_seal_number', 255)->nullable();
            $table->bigInteger('joc_created_by');
            $table->dateTime('joc_created_on');
            $table->bigInteger('joc_updated_by')->nullable();
            $table->dateTime('joc_updated_on')->nullable();
            $table->bigInteger('joc_deleted_by')->nullable();
            $table->dateTime('joc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_container');
    }
}
