<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_module', function (Blueprint $table) {
            $table->bigIncrements('tm_id');
            $table->string('tm_name', 100);
            $table->char('tm_active', 1)->default('Y');
            $table->integer('tm_created_by');
            $table->dateTime('tm_created_on');
            $table->integer('tm_updated_by')->nullable();
            $table->dateTime('tm_updated_on')->nullable();
            $table->integer('tm_deleted_by')->nullable();
            $table->dateTime('tm_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => TransportModuleSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transport_module');
    }
}
