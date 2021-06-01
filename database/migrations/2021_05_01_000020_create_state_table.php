<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state', function (Blueprint $table) {
            $table->uuid('stt_id')->primary();
            $table->uuid('stt_cnt_id')->unsigned();
            $table->foreign('stt_cnt_id', 'tbl_stt_cnt_id_fkey')->references('cnt_id')->on('country');
            $table->string('stt_name', 128);
            $table->string('stt_iso', 64)->nullable();
            $table->char('stt_active', 1)->default('Y');
            $table->uuid('stt_created_by');
            $table->dateTime('stt_created_on');
            $table->uuid('stt_updated_by')->nullable();
            $table->dateTime('stt_updated_on')->nullable();
            $table->uuid('stt_deleted_by')->nullable();
            $table->dateTime('stt_deleted_on')->nullable();
            $table->string('stt_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state');
    }
}
