<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serial_code', function (Blueprint $table) {
            $table->uuid('sc_id')->primary();
            $table->string('sc_code', 128);
            $table->string('sc_description', 256);
            $table->char('sc_active', 1)->default('Y');
            $table->uuid('sc_created_by');
            $table->dateTime('sc_created_on');
            $table->uuid('sc_updated_by')->nullable();
            $table->dateTime('sc_updated_on')->nullable();
            $table->uuid('sc_deleted_by')->nullable();
            $table->dateTime('sc_deleted_on')->nullable();
            $table->string('sc_deleted_reason', 256)->nullable();
            $table->unique('sc_code', 'tbl_sc_code_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SerialCodeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serial_code');
    }
}
