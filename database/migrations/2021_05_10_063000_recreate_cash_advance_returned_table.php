<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateCashAdvanceReturnedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_returned', function (Blueprint $table) {
            $table->bigIncrements('crt_id');
            $table->bigInteger('crt_ca_id')->unsigned();
            $table->foreign('crt_ca_id', 'tbl_crt_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->bigInteger('crt_created_by');
            $table->dateTime('crt_created_on');
            $table->bigInteger('crt_updated_by')->nullable();
            $table->dateTime('crt_updated_on')->nullable();
            $table->bigInteger('crt_deleted_by')->nullable();
            $table->dateTime('crt_deleted_on')->nullable();
            $table->string('crt_deleted_reason', 256)->nullable();
            $table->uuid('crt_uid');
            $table->unique('crt_uid', 'tbl_crt_uid_unique');
        });
        Schema::table('cash_advance', function (Blueprint $table) {
            $table->bigInteger('ca_crt_id')->unsigned()->nullable();
            $table->foreign('ca_crt_id', 'tbl_ca_crt_id_fkey')->references('crt_id')->on('cash_advance_returned');
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
            $table->dropForeign('tbl_ca_crt_id_fkey');
            $table->dropColumn('ca_crt_id');
        });
        Schema::dropIfExists('cash_advance_returned');
    }
}
