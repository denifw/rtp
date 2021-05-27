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
            $table->bigIncrements('sc_id');
            $table->string('sc_code', 125);
            $table->string('sc_description', 255);
            $table->char('sc_active', 1)->default('Y');
            $table->bigInteger('sc_created_by');
            $table->dateTime('sc_created_on');
            $table->bigInteger('sc_updated_by')->nullable();
            $table->dateTime('sc_updated_on')->nullable();
            $table->bigInteger('sc_deleted_by')->nullable();
            $table->dateTime('sc_deleted_on')->nullable();
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
