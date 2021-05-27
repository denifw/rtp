<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_detail', function (Blueprint $table) {
            $table->bigIncrements('dsd_id');
            $table->bigInteger('dsd_dsh_id')->unsigned();
            $table->foreign('dsd_dsh_id', 'tbl_dsd_dsh_id_foreign')->references('dsh_id')->on('dashboard');
            $table->bigInteger('dsd_dsi_id')->unsigned();
            $table->foreign('dsd_dsi_id', 'tbl_dsd_dsi_id_foreign')->references('dsi_id')->on('dashboard_item');
            $table->string('dsd_title', 255);
            $table->bigInteger('dsd_grid_large')->default(3);
            $table->bigInteger('dsd_grid_medium')->default(4);
            $table->bigInteger('dsd_grid_small')->default(6);
            $table->bigInteger('dsd_grid_xsmall')->default(12);
            $table->bigInteger('dsd_height')->default(300);
            $table->string('dsd_color')->default('#000000');
            $table->bigInteger('dsd_order');
            $table->jsonb('dsd_parameter')->nullable();
            $table->bigInteger('dsd_created_by');
            $table->dateTime('dsd_created_on');
            $table->bigInteger('dsd_updated_by')->nullable();
            $table->dateTime('dsd_updated_on')->nullable();
            $table->bigInteger('dsd_deleted_by')->nullable();
            $table->dateTime('dsd_deleted_on')->nullable();
         });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DashboardDetailSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_detail');
    }
}
