<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_setting', function (Blueprint $table) {
            $table->bigIncrements('ss_id');
            $table->string('ss_relation', 225);
            $table->bigInteger('ss_lg_id')->unsigned();
            $table->foreign('ss_lg_id', 'tbl_ss_lg_id_foreign')->references('lg_id')->on('languages');
            $table->bigInteger('ss_cur_id')->unsigned();
            $table->foreign('ss_cur_id', 'tbl_ss_cur_id_foreign')->references('cur_id')->on('currency');
            $table->bigInteger('ss_decimal_number');
            $table->char('ss_decimal_separator', 1)->default('.');
            $table->char('ss_thousand_separator', 1)->default(',');
            $table->string('ss_logo', 225);
            $table->string('ss_name_space', 225);
            $table->char('ss_system', 1)->default('N');
            $table->char('ss_active', 1)->default('Y');
            $table->bigInteger('ss_created_by');
            $table->dateTime('ss_created_on');
            $table->bigInteger('ss_updated_by')->nullable();
            $table->dateTime('ss_updated_on')->nullable();
            $table->bigInteger('ss_deleted_by')->nullable();
            $table->dateTime('ss_deleted_on')->nullable();
            $table->unique('ss_name_space', 'tbl_ss_name_space_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SystemSettingSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_setting');
    }
}
