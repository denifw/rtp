<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('rt_pintar');
        Schema::create('rt_pintar', function (Blueprint $table) {
            $table->uuid('rtp_id')->primary();
            $table->string('rtp_unit', 128);
            $table->string('rtp_code', 128);
            $table->string('rtp_description', 256)->nullable();
            $table->float('rtp_amount')->nullable();
            $table->integer('rtp_month')->nullable();
            $table->integer('rtp_year')->nullable();
            $table->char('rtp_status', 1)->nullable();
            $table->string('rtp_type', 1)->nullable();
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
        //
    }
}
