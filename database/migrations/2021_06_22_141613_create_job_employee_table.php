<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_employee', function (Blueprint $table) {
            $table->uuid('jem_id')->primary();
            $table->uuid('jem_jo_id')->unsigned();
            $table->foreign('jem_jo_id', 'tbl_jem_jo_id_fkey')->references('jo_id')->on('job_order');
            $table->uuid('jem_em_id')->unsigned();
            $table->foreign('jem_em_id', 'tbl_jem_em_id_fkey')->references('em_id')->on('employee');
            $table->char('jem_type', 1);
            $table->float('jem_shift_one')->nullable();
            $table->float('jem_shift_two')->nullable();
            $table->float('jem_shift_three')->nullable();
            $table->uuid('jem_created_by');
            $table->dateTime('jem_created_on');
            $table->uuid('jem_updated_by')->nullable();
            $table->dateTime('jem_updated_on')->nullable();
            $table->uuid('jem_deleted_by')->nullable();
            $table->dateTime('jem_deleted_on')->nullable();
            $table->string('jem_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_employee');
    }
}
