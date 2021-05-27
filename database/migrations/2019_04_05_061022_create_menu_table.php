<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->bigIncrements('mn_id');
            $table->string('mn_name', 125);
            $table->bigInteger('mn_parent')->nullable();
            $table->bigInteger('mn_order');
            $table->string('mn_icon', '125');
            $table->char('mn_active', 1)->default('Y');
            $table->bigInteger('mn_created_by');
            $table->dateTime('mn_created_on');
            $table->bigInteger('mn_updated_by')->nullable();
            $table->dateTime('mn_updated_on')->nullable();
            $table->bigInteger('mn_deleted_by')->nullable();
            $table->dateTime('mn_deleted_on')->nullable();
            $table->unique(['mn_name', 'mn_parent'], 'tbl_mn_name_parent_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => MenuSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
