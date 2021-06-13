<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serial_history', function (Blueprint $table) {
            $table->uuid('sh_id')->primary();
            $table->uuid('sh_sn_id')->unsigned();
            $table->foreign('sh_sn_id', 'tbl_sh_sn_id_fkey')->references('sn_id')->on('serial_number');
            $table->uuid('sh_rel_id')->unsigned()->nullable();
            $table->foreign('sh_rel_id', 'tbl_sh_rel_id_fkey')->references('rel_id')->on('relation');
            $table->char('sh_year', 4)->nullable();
            $table->char('sh_month', 2)->nullable();
            $table->bigInteger('sh_number');
            $table->dateTime('sh_created_on');
            $table->uuid('sh_created_by');
            $table->dateTime('sh_updated_on')->nullable();
            $table->uuid('sh_updated_by')->nullable();
            $table->dateTime('sh_deleted_on')->nullable();
            $table->uuid('sh_deleted_by')->nullable();
            $table->string('sh_deleted_reason', 256)->nullable();
            $table->unique(['sh_sn_id', 'sh_rel_id', 'sh_year', 'sh_month'], 'tbl_sh_sn_sh_year_month_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serial_history');
    }
}
