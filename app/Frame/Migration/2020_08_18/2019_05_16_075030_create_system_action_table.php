<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_action', function (Blueprint $table) {
            $table->bigIncrements('sac_id');
            $table->bigInteger('sac_ss_id')->unsigned();
            $table->foreign('sac_ss_id', 'tbl_sac_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('sac_srt_id')->unsigned();
            $table->foreign('sac_srt_id', 'tbl_sac_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->bigInteger('sac_ac_id')->unsigned();
            $table->foreign('sac_ac_id', 'tbl_sac_ac_id_foreign')->references('ac_id')->on('action');
            $table->integer('sac_order');
            $table->bigInteger('sac_created_by');
            $table->dateTime('sac_created_on');
            $table->bigInteger('sac_updated_by')->nullable();
            $table->dateTime('sac_updated_on')->nullable();
            $table->bigInteger('sac_deleted_by')->nullable();
            $table->dateTime('sac_deleted_on')->nullable();
            $table->unique(['sac_ss_id', 'sac_ac_id'], 'tbl_sac_ss_ac_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SystemActionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_action');
    }
}
