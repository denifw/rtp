<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_category', function (Blueprint $table) {
            $table->bigIncrements('pc_id');
            $table->string('pc_name', 55);
            $table->string('pc_route', 55)->nullable();
            $table->char('pc_active', 1)->default('Y');
            $table->bigInteger('pc_created_by');
            $table->dateTime('pc_created_on');
            $table->bigInteger('pc_updated_by')->nullable();
            $table->dateTime('pc_updated_on')->nullable();
            $table->bigInteger('pc_deleted_by')->nullable();
            $table->dateTime('pc_deleted_on')->nullable();
            $table->unique('pc_name', 'tbl_pc_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => PageCategorySeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_category');
    }
}
