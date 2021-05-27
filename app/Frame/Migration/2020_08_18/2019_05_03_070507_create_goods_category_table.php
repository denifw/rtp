<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_category', function (Blueprint $table) {
            $table->bigIncrements('gdc_id');
            $table->bigInteger('gdc_ss_id')->unsigned();
            $table->foreign('gdc_ss_id', 'tbl_gdc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('gdc_name', 150);
            $table->char('gdc_active', 1)->default('Y');
            $table->bigInteger('gdc_created_by');
            $table->dateTime('gdc_created_on');
            $table->bigInteger('gdc_updated_by')->nullable();
            $table->dateTime('gdc_updated_on')->nullable();
            $table->bigInteger('gdc_deleted_by')->nullable();
            $table->dateTime('gdc_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_category');
    }
}
