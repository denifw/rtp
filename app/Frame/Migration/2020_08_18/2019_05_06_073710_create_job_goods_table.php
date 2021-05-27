<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_goods', function (Blueprint $table) {
            $table->bigIncrements('jog_id');
            $table->string('jog_serial_number', 255);
            $table->bigInteger('jog_jo_id')->unsigned();
            $table->foreign('jog_jo_id', 'tbl_jog_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('jog_gd_id')->unsigned()->nullable();
            $table->foreign('jog_gd_id', 'tbl_jog_gd_id_foreign')->references('gd_id')->on('goods');
            $table->string('jog_name', 255);
            $table->float('jog_quantity')->nullable();
            $table->bigInteger('jog_uom_id')->unsigned()->nullable();
            $table->foreign('jog_uom_id', 'tbl_jog_uom_id_foreign')->references('uom_id')->on('unit');
            $table->string('jog_production_number', 255)->nullable();
            $table->date('jog_production_date')->nullable();
            $table->date('jog_available_date')->nullable();
            $table->float('jog_length')->nullable();
            $table->float('jog_width')->nullable();
            $table->float('jog_height')->nullable();
            $table->float('jog_gross_weight')->nullable();
            $table->float('jog_net_weight')->nullable();
            $table->float('jog_volume')->nullable();
            $table->bigInteger('jog_created_by');
            $table->dateTime('jog_created_on');
            $table->bigInteger('jog_updated_by')->nullable();
            $table->dateTime('jog_updated_on')->nullable();
            $table->bigInteger('jog_deleted_by')->nullable();
            $table->dateTime('jog_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_goods');
    }
}
