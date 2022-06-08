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
            $table->uuid('pc_id')->primary();
            $table->string('pc_name', 64);
            $table->string('pc_code', 64);
            $table->string('pc_route', 64)->nullable();
            $table->char('pc_active', 1)->default('Y');
            $table->uuid('pc_created_by');
            $table->dateTime('pc_created_on');
            $table->uuid('pc_updated_by')->nullable();
            $table->dateTime('pc_updated_on')->nullable();
            $table->uuid('pc_deleted_by')->nullable();
            $table->dateTime('pc_deleted_on')->nullable();
            $table->unique('pc_code', 'tbl_pc_code_unique');
            $table->string('pc_deleted_reason', 256)->nullable();
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
