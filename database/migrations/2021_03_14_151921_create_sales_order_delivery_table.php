<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_delivery', function (Blueprint $table) {
            $table->bigIncrements('sdl_id');
            $table->bigInteger('sdl_so_id')->unsigned();
            $table->foreign('sdl_so_id', 'tbl_sdl_so_id_foreign')->references('so_id')->on('sales_order');

            $table->bigInteger('sdl_rel_id')->unsigned();
            $table->foreign('sdl_rel_id', 'tbl_sdl_rel_id_foreign')->references('rel_id')->on('relation');
            $table->bigInteger('sdl_of_id')->unsigned();
            $table->foreign('sdl_of_id', 'tbl_sdl_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('sdl_pic_id')->unsigned()->nullable();
            $table->foreign('sdl_pic_id', 'tbl_sdl_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->string('sdl_reference', 255)->nullable();

            $table->bigInteger('sdl_sog_id')->unsigned()->nullable();
            $table->foreign('sdl_sog_id', 'tbl_sdl_sog_id_foreign')->references('sog_id')->on('sales_order_goods');
            $table->float('sdl_quantity')->nullable();
            $table->char('sdl_type', 1);

            $table->bigInteger('sdl_created_by');
            $table->dateTime('sdl_created_on');
            $table->bigInteger('sdl_updated_by')->nullable();
            $table->dateTime('sdl_updated_on')->nullable();
            $table->bigInteger('sdl_deleted_by')->nullable();
            $table->dateTime('sdl_deleted_on')->nullable();
            $table->string('sdl_deleted_reason', 256)->nullable();
            $table->uuid('sdl_uid');
            $table->unique('sdl_uid', 'tbl_sdl_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_delivery');
    }
}
