<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service', function (Blueprint $table) {
            $table->bigIncrements('srv_id');
            $table->string('srv_name', 125);
            $table->char('srv_active', 1)->default('Y');
            $table->bigInteger('srv_created_by');
            $table->dateTime('srv_created_on');
            $table->bigInteger('srv_updated_by')->nullable();
            $table->dateTime('srv_updated_on')->nullable();
            $table->bigInteger('srv_deleted_by')->nullable();
            $table->dateTime('srv_deleted_on')->nullable();
            $table->unique('srv_name', 'tbl_srv_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ServiceSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service');
    }
}
