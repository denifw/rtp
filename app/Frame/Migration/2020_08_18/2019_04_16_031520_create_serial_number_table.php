<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialNumberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serial_number', function (Blueprint $table) {
            $table->bigIncrements('sn_id');
            $table->bigInteger('sn_sc_id')->unsigned();
            $table->foreign('sn_sc_id', 'tbl_sn_sc_id_foreign')->references('sc_id')->on('serial_code');
            $table->bigInteger('sn_ss_id')->unsigned();
            $table->foreign('sn_ss_id', 'tbl_sn_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('sn_of_id')->unsigned()->nullable();
            $table->foreign('sn_of_id', 'tbl_sn_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('sn_rel_id')->unsigned()->nullable();
            $table->foreign('sn_rel_id', 'tbl_sn_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('sn_srv_id')->unsigned()->nullable();
            $table->foreign('sn_srv_id', 'tbl_sn_srv_id_foreign')->references('srv_id')->on('service');
            $table->bigInteger('sn_srt_id')->unsigned()->nullable();
            $table->foreign('sn_srt_id', 'tbl_sn_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->string('sn_separator', 5)->nullable();
            $table->string('sn_prefix', 10)->nullable();
            $table->char('sn_yearly', 1)->default('Y');
            $table->char('sn_monthly', 1)->default('Y');
            $table->bigInteger('sn_length')->default(10);
            $table->bigInteger('sn_increment')->default(1);
            $table->string('sn_postfix', 10)->nullable();
            $table->char('sn_active', 1)->default('Y');
            $table->bigInteger('sn_created_by');
            $table->dateTime('sn_created_on');
            $table->bigInteger('sn_updated_by')->nullable();
            $table->dateTime('sn_updated_on')->nullable();
            $table->bigInteger('sn_deleted_by')->nullable();
            $table->dateTime('sn_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SerialNumberSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serial_number');

    }
}
