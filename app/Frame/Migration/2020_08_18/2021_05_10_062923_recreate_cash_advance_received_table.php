<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateCashAdvanceReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_received', function (Blueprint $table) {
            $table->bigIncrements('crc_id');
            $table->bigInteger('crc_ca_id')->unsigned();
            $table->foreign('crc_ca_id', 'tbl_crc_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->bigInteger('crc_created_by');
            $table->dateTime('crc_created_on');
            $table->bigInteger('crc_updated_by')->nullable();
            $table->dateTime('crc_updated_on')->nullable();
            $table->bigInteger('crc_deleted_by')->nullable();
            $table->dateTime('crc_deleted_on')->nullable();
            $table->string('crc_deleted_reason', 256)->nullable();
            $table->uuid('crc_uid');
            $table->unique('crc_uid', 'tbl_crc_uid_unique');
        });
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->bigInteger('ca_crc_id')->unsigned()->nullable();
            $table->foreign('ca_crc_id', 'tbl_ca_crc_id_fkey')->references('crc_id')->on('cash_advance_received');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->dropForeign('tbl_ca_crc_id_fkey');
            $table->dropColumn('ca_crc_id');
        });
        Schema::dropIfExists('cash_advance_received');
    }
}
