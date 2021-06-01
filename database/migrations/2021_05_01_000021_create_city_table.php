<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city', function (Blueprint $table) {
            $table->uuid('cty_id')->primary();
            $table->uuid('cty_cnt_id')->unsigned();
            $table->foreign('cty_cnt_id', 'tbl_cty_cnt_id_fkey')->references('cnt_id')->on('country');
            $table->uuid('cty_stt_id')->unsigned();
            $table->foreign('cty_stt_id', 'tbl_cty_stt_id_fkey')->references('stt_id')->on('state');
            $table->string('cty_name', 128);
            $table->string('cty_iso', 64)->nullable();
            $table->char('cty_active', 1)->default('Y');
            $table->uuid('cty_created_by');
            $table->dateTime('cty_created_on');
            $table->uuid('cty_updated_by')->nullable();
            $table->dateTime('cty_updated_on')->nullable();
            $table->uuid('cty_deleted_by')->nullable();
            $table->dateTime('cty_deleted_on')->nullable();
            $table->string('cty_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city');
    }
}
