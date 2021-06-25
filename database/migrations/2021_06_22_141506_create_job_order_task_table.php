<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_order_task', function (Blueprint $table) {
            $table->uuid('jot_id')->primary();
            $table->uuid('jot_jo_id')->unsigned();
            $table->foreign('jot_jo_id', 'tbl_jot_jo_id_fkey')->references('jo_id')->on('job_order');
            $table->string('jot_description', 256);
            $table->uuid('jot_rel_id')->unsigned()->nullable();
            $table->foreign('jot_rel_id', 'tbl_jot_rel_id_fkey')->references('rel_id')->on('relation');
            $table->uuid('jot_cp_id')->unsigned()->nullable();
            $table->foreign('jot_cp_id', 'tbl_jot_cp_id_fkey')->references('cp_id')->on('contact_person');
            $table->string('jot_notes', 256)->nullable();
            $table->float('jot_portion')->nullable();
            $table->float('jot_progress')->nullable();
            $table->uuid('jot_created_by');
            $table->dateTime('jot_created_on');
            $table->uuid('jot_updated_by')->nullable();
            $table->dateTime('jot_updated_on')->nullable();
            $table->uuid('jot_deleted_by')->nullable();
            $table->dateTime('jot_deleted_on')->nullable();
            $table->string('jot_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_order_task');
    }
}
