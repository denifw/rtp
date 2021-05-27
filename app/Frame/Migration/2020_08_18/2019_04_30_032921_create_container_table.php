<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container', function (Blueprint $table) {
            $table->bigIncrements('ct_id');
            $table->string('ct_name', 125);
            $table->float('ct_length');
            $table->float('ct_height');
            $table->float('ct_width');
            $table->float('ct_volume');
            $table->float('ct_max_weight');
            $table->char('ct_active', 1)->default('Y');
            $table->bigInteger('ct_created_by');
            $table->dateTime('ct_created_on');
            $table->bigInteger('ct_updated_by')->nullable();
            $table->dateTime('ct_updated_on')->nullable();
            $table->bigInteger('ct_deleted_by')->nullable();
            $table->dateTime('ct_deleted_on')->nullable();
            $table->unique('ct_name', 'tbl_ct_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ContainerSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('container');
    }
}
