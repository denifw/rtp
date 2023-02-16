<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRtPintarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rt_pintar', function (Blueprint $table) {
            $table->uuid('rtp_id')->primary();
            $table->string('rtp_code', 128);
            $table->string('rtp_description', 256)->nullable();
            $table->float('rtp_amount')->nullable();
            $table->integer('rtp_month')->nullable();
            $table->integer('rtp_year')->nullable();
            $table->string('rtp_status', 256)->nullable();
            $table->string('rtp_status_text', 256)->nullable();
            $table->string('rtp_payment_time', 256)->nullable();
            $table->string('rtp_contact', 256)->nullable();
            $table->string('rtp_block', 256)->nullable();
            $table->string('rtp_number', 256)->nullable();
            $table->uuid('rtp_created_by');
            $table->dateTime('rtp_created_on');
            $table->uuid('rtp_updated_by')->nullable();
            $table->dateTime('rtp_updated_on')->nullable();
            $table->uuid('rtp_deleted_by')->nullable();
            $table->dateTime('rtp_deleted_on')->nullable();
            $table->string('rtp_deleted_reason', 256)->nullable();
            $table->unique('rtp_code', 'tbl_rtp_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rt_pintar');
    }
}
