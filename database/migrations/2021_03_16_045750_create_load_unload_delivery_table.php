<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoadUnloadDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('load_unload_delivery', function (Blueprint $table) {
            $table->bigIncrements('lud_id');
            $table->bigInteger('lud_jdl_id')->unsigned();
            $table->foreign('lud_jdl_id', 'tbl_lud_jdl_id_fkey')->references('jdl_id')->on('job_delivery');
            $table->bigInteger('lud_sdl_id')->unsigned()->nullable();
            $table->foreign('lud_sdl_id', 'tbl_lud_sdl_id_fkey')->references('sdl_id')->on('sales_order_delivery');
            $table->bigInteger('lud_rel_id')->unsigned();
            $table->foreign('lud_rel_id', 'tbl_lud_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('lud_of_id')->unsigned();
            $table->foreign('lud_of_id', 'tbl_lud_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('lud_pic_id')->unsigned()->nullable();
            $table->foreign('lud_pic_id', 'tbl_lud_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('lud_reference', 255)->nullable();
            $table->bigInteger('lud_sog_id')->unsigned();
            $table->foreign('lud_sog_id', 'tbl_lud_sog_id_fkey')->references('sog_id')->on('sales_order_goods');
            $table->float('lud_quantity')->nullable();
            $table->char('lud_type', 1);
            $table->float('lud_qty_good')->nullable();
            $table->float('lud_qty_damage')->nullable();
            $table->dateTime('lud_ata_on')->nullable();
            $table->dateTime('lud_start_on')->nullable();
            $table->dateTime('lud_end_on')->nullable();
            $table->dateTime('lud_atd_on')->nullable();
            $table->bigInteger('lud_created_by');
            $table->dateTime('lud_created_on');
            $table->bigInteger('lud_updated_by')->nullable();
            $table->dateTime('lud_updated_on')->nullable();
            $table->bigInteger('lud_deleted_by')->nullable();
            $table->dateTime('lud_deleted_on')->nullable();
            $table->string('lud_deleted_reason', 256)->nullable();
            $table->uuid('lud_uid');
            $table->unique('lud_uid', 'tbl_lud_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('load_unload_delivery');
    }
}
