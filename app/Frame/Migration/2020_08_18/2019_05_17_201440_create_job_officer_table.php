<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOfficerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_officer', function (Blueprint $table) {
            $table->bigIncrements('joo_id');
            $table->bigInteger('joo_jo_id')->unsigned();
            $table->foreign('joo_jo_id', 'tbl_joo_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('joo_us_id')->unsigned();
            $table->foreign('joo_us_id', 'tbl_joo_ac_id_foreign')->references('us_id')->on('users');
            $table->bigInteger('joo_created_by');
            $table->dateTime('joo_created_on');
            $table->bigInteger('joo_updated_by')->nullable();
            $table->dateTime('joo_updated_on')->nullable();
            $table->bigInteger('joo_deleted_by')->nullable();
            $table->dateTime('joo_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => JobOfficerSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_officer');
    }
}
