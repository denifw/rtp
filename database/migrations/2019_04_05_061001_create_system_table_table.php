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
            $table->uuid('prd_uid')->primary();
            $table->string('st_name', 255);
            $table->string('st_prefix', 255);
            $table->string('st_path', 255);
            $table->char('st_active', 1)->default('Y');
            $table->uuid('st_created_by');
            $table->dateTime('st_created_on');
            $table->uuid('st_updated_by')->nullable();
            $table->dateTime('st_updated_on')->nullable();
            $table->uuid('st_deleted_by')->nullable();
            $table->dateTime('st_deleted_on')->nullable();
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
