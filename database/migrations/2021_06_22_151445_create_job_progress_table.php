<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_progress', function (Blueprint $table) {
            $table->uuid('jop_id')->primary();
            $table->uuid('jop_jot_id')->unsigned();
            $table->foreign('jop_jot_id', 'tbl_jop_jot_id_fkey')->references('jot_id')->on('job_order_task');
            $table->date('jop_date');
            $table->float('jop_progress');
            $table->uuid('jop_created_by');
            $table->dateTime('jop_created_on');
            $table->uuid('jop_updated_by')->nullable();
            $table->dateTime('jop_updated_on')->nullable();
            $table->uuid('jop_deleted_by')->nullable();
            $table->dateTime('jop_deleted_on')->nullable();
            $table->string('jop_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_progress');
    }
}
