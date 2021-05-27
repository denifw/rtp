<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_action', function (Blueprint $table) {
            $table->bigIncrements('jac_id');
            $table->bigInteger('jac_jo_id')->unsigned();
            $table->foreign('jac_jo_id', 'tbl_jac_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jac_ac_id')->unsigned();
            $table->foreign('jac_ac_id', 'tbl_jac_ac_id_foreign')->references('ac_id')->on('action');
            $table->bigInteger('jac_start_by')->unsigned()->nullable();
            $table->foreign('jac_start_by', 'tbl_jac_start_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jac_start_on')->nullable();
            $table->bigInteger('jac_end_by')->unsigned()->nullable();
            $table->foreign('jac_end_by', 'tbl_jac_end_by_foreign')->references('us_id')->on('users');
            $table->dateTime('jac_end_on')->nullable();
            $table->integer('jac_order');
            $table->string('jac_remark', 255)->nullable();
            $table->date('jac_start_date')->nullable();
            $table->time('jac_start_time')->nullable();
            $table->date('jac_end_date')->nullable();
            $table->time('jac_end_time')->nullable();
            $table->char('jac_active', 1)->default('Y');
            $table->bigInteger('jac_created_by');
            $table->dateTime('jac_created_on');
            $table->bigInteger('jac_updated_by')->nullable();
            $table->dateTime('jac_updated_on')->nullable();
            $table->bigInteger('jac_deleted_by')->nullable();
            $table->dateTime('jac_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => JobActionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_action');
    }
}
