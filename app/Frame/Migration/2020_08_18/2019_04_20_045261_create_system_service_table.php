<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_service', function (Blueprint $table) {
            $table->bigIncrements('ssr_id');
            $table->bigInteger('ssr_ss_id')->unsigned();
            $table->foreign('ssr_ss_id', 'tbl_ssr_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('ssr_srv_id')->unsigned();
            $table->foreign('ssr_srv_id', 'tbl_ssr_srv_id_foreign')->references('srv_id')->on('service');
            $table->char('ssr_active', 1)->default('Y');
            $table->bigInteger('ssr_created_by');
            $table->dateTime('ssr_created_on');
            $table->bigInteger('ssr_updated_by')->nullable();
            $table->dateTime('ssr_updated_on')->nullable();
            $table->bigInteger('ssr_deleted_by')->nullable();
            $table->dateTime('ssr_deleted_on')->nullable();
            $table->unique(['ssr_srv_id', 'ssr_ss_id'], 'tbl_ssr_srv_ss_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SystemServiceSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_service');
    }
}
