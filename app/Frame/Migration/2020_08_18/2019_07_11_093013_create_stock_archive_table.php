<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_archive', function (Blueprint $table) {
            $table->bigIncrements('sa_id');
            $table->bigInteger('sa_last_archive')->nullable();
            $table->bigInteger('sa_ss_id')->unsigned();
            $table->foreign('sa_ss_id', 'tbl_sa_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('sa_rel_id')->unsigned();
            $table->foreign('sa_rel_id', 'tbl_sa_rel_id_foreign')->references('rel_id')->on('relation');
            $table->date('sa_date');
            $table->string('sa_delete_reason', 255)->nullable();
            $table->dateTime('sa_complete_on')->nullable();
            $table->bigInteger('sa_created_by');
            $table->dateTime('sa_created_on');
            $table->bigInteger('sa_updated_by')->nullable();
            $table->dateTime('sa_updated_on')->nullable();
            $table->bigInteger('sa_deleted_by')->nullable();
            $table->dateTime('sa_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_archive');
    }
}
