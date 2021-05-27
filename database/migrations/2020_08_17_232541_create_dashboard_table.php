<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard', function (Blueprint $table) {
            $table->bigIncrements('dsh_id');
            $table->bigInteger('dsh_ss_id')->unsigned();
            $table->foreign('dsh_ss_id', 'tbl_dsh_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('dsh_us_id')->unsigned();
            $table->foreign('dsh_us_id', 'tbl_dsh_us_id_foreign')->references('us_id')->on('users');
            $table->string('dsh_name', 255);
            $table->string('dsh_description', 255)->nullable();
            $table->bigInteger('dsh_order');
            $table->bigInteger('dsh_created_by');
            $table->dateTime('dsh_created_on');
            $table->bigInteger('dsh_updated_by')->nullable();
            $table->dateTime('dsh_updated_on')->nullable();
            $table->bigInteger('dsh_deleted_by')->nullable();
            $table->dateTime('dsh_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DashboardSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard');
    }
}
