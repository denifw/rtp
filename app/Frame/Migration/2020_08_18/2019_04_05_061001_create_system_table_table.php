<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_table', function (Blueprint $table) {
            $table->bigIncrements('st_id');
            $table->string('st_name', 255);
            $table->string('st_prefix', 255);
            $table->string('st_path', 255);
            $table->char('st_active', 1)->default('Y');
            $table->bigInteger('st_created_by');
            $table->dateTime('st_created_on');
            $table->bigInteger('st_updated_by')->nullable();
            $table->dateTime('st_updated_on')->nullable();
            $table->bigInteger('st_deleted_by')->nullable();
            $table->dateTime('st_deleted_on')->nullable();
            $table->unique('st_prefix', 'tbl_st_prefix_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SystemTableSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_table');
    }
}
