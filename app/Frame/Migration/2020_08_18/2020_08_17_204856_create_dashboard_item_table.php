<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_item', function (Blueprint $table) {
            $table->bigIncrements('dsi_id');
            $table->string('dsi_title', 255);
            $table->string('dsi_code', 255);
            $table->string('dsi_route', 255);
            $table->string('dsi_path', 255);
            $table->string('dsi_description', 255)->nullable();
            $table->bigInteger('dsi_grid_large')->default(3);
            $table->bigInteger('dsi_grid_medium')->default(4);
            $table->bigInteger('dsi_grid_small')->default(6);
            $table->bigInteger('dsi_grid_xsmall')->default(12);
            $table->bigInteger('dsi_height')->default(300);
            $table->string('dsi_color')->default('#000000');
            $table->bigInteger('dsi_order');
            $table->bigInteger('dsi_created_by');
            $table->dateTime('dsi_created_on');
            $table->bigInteger('dsi_updated_by')->nullable();
            $table->dateTime('dsi_updated_on')->nullable();
            $table->bigInteger('dsi_deleted_by')->nullable();
            $table->dateTime('dsi_deleted_on')->nullable();
            $table->unique(['dsi_path'], 'tbl_dsi_path_unique');
            $table->unique(['dsi_code'], 'tbl_dsi_code_unique');
            $table->unique(['dsi_route'], 'tbl_dsi_route_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DashboardItemSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_item');
    }
}
