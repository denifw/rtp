<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page', function (Blueprint $table) {
            $table->bigIncrements('pg_id');
            $table->string('pg_title', 125);
            $table->string('pg_description', 255);
            $table->string('pg_route', 125)->nullable();
            $table->bigInteger('pg_mn_id')->unsigned()->nullable();
            $table->foreign('pg_mn_id', 'tbl_pg_mn_id_foreign')->references('mn_id')->on('menu');
            $table->bigInteger('pg_pc_id')->unsigned();
            $table->foreign('pg_pc_id', 'tbl_pg_pc_id_foreign')->references('pc_id')->on('page_category');
            $table->string('pg_icon', 125)->nullable();
            $table->bigInteger('pg_order')->nullable();
            $table->char('pg_default', 1)->default('Y');
            $table->char('pg_system', 1)->default('N');
            $table->char('pg_active', 1)->default('Y');
            $table->bigInteger('pg_created_by');
            $table->dateTime('pg_created_on');
            $table->bigInteger('pg_updated_by')->nullable();
            $table->dateTime('pg_updated_on')->nullable();
            $table->bigInteger('pg_deleted_by')->nullable();
            $table->dateTime('pg_deleted_on')->nullable();
            $table->unique(['pg_route', 'pg_pc_id'], 'tbl_pg_route_pc_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => PageSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page');
    }
}
