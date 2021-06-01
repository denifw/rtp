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
            $table->uuid('sn_id')->primary();
            $table->uuid('sn_ss_id')->unsigned();
            $table->foreign('sn_ss_id', 'tbl_sn_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->uuid('sn_sc_id')->unsigned();
            $table->foreign('sn_sc_id', 'tbl_sn_sc_id_fkey')->references('sc_id')->on('serial_code');
            $table->uuid('sn_of_id')->unsigned()->nullable();
            $table->foreign('sn_of_id', 'tbl_sn_of_id_fkey')->references('of_id')->on('office');
            $table->char('sn_relation', 1)->default('Y');
            $table->string('sn_separator', 4)->nullable();
            $table->string('sn_prefix', 16)->nullable();
            $table->char('sn_yearly', 1)->default('Y');
            $table->char('sn_monthly', 1)->default('Y');
            $table->integer('sn_length')->default(10);
            $table->integer('sn_increment')->default(1);
            $table->string('sn_postfix', 10)->nullable();
            $table->char('sn_active', 1)->default('Y');
            $table->uuid('sn_created_by');
            $table->dateTime('sn_created_on');
            $table->uuid('sn_updated_by')->nullable();
            $table->dateTime('sn_updated_on')->nullable();
            $table->uuid('sn_deleted_by')->nullable();
            $table->dateTime('sn_deleted_on')->nullable();
            $table->string('sn_deleted_reason', 256)->nullable();
        });
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
