<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsNumberHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_number_history', function (Blueprint $table) {
            $table->bigIncrements('gnh_id');
            $table->bigInteger('gnh_gpf_id')->unsigned();
            $table->foreign('gnh_gpf_id', 'tbl_gnh_gpf_id_foreign')->references('gpf_id')->on('goods_prefix');
            $table->string('gnh_year', 4)->nullable();
            $table->string('gnh_month', 2)->nullable();
            $table->float('gnh_number');
            $table->dateTime('gnh_created_on');
            $table->bigInteger('gnh_created_by');
            $table->dateTime('gnh_updated_on')->nullable();
            $table->bigInteger('gnh_updated_by')->nullable();
            $table->dateTime('gnh_deleted_on')->nullable();
            $table->bigInteger('gnh_deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_number_history');
    }
}
