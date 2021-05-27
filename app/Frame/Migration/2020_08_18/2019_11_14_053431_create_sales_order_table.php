<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order', function (Blueprint $table) {
            $table->bigIncrements('so_id');
            $table->bigInteger('so_ss_id')->unsigned();
            $table->foreign('so_ss_id', 'tbl_so_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('so_number', 255);
            $table->bigInteger('so_rel_id')->unsigned()->nullable();
            $table->foreign('so_rel_id', 'tbl_so_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('so_customer_ref', 255)->nullable();
            $table->bigInteger('so_pic_id')->unsigned()->nullable();
            $table->foreign('so_pic_id', 'tbl_so_pic_id_foreign')->references('cp_id')->on('contact_person');
            $table->bigInteger('so_order_of_id')->unsigned();
            $table->foreign('so_order_of_id', 'tbl_so_order_of_id_foreign')->references('of_id')->on('office');
            $table->bigInteger('so_invoice_of_id')->unsigned()->nullable();
            $table->foreign('so_invoice_of_id', 'tbl_so_invoice_of_id_foreign')->references('of_id')->on('office');
            $table->date('so_order_date');
            $table->date('so_planning_date');
            $table->time('so_planning_time');

            $table->char('so_container', 1)->nullable();
            $table->float('so_party');
            $table->string('so_bl_ref', 255)->nullable();
            $table->string('so_aju_ref', 255)->nullable();
            $table->string('so_packing_ref', 255)->nullable();
            $table->string('so_notes', 255)->nullable();

            $table->bigInteger('so_publish_by')->unsigned()->nullable();
            $table->foreign('so_publish_by', 'tbl_so_publish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('so_publish_on')->nullable();

            $table->bigInteger('so_finish_by')->unsigned()->nullable();
            $table->foreign('so_finish_by', 'tbl_so_finish_by_foreign')->references('us_id')->on('users');
            $table->dateTime('so_finish_on')->nullable();

            $table->string('so_deleted_reason', 255)->nullable();
            $table->bigInteger('so_created_by');
            $table->dateTime('so_created_on');
            $table->bigInteger('so_updated_by')->nullable();
            $table->dateTime('so_updated_on')->nullable();
            $table->bigInteger('so_deleted_by')->nullable();
            $table->dateTime('so_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order');
    }
}
