<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_archive', function (Blueprint $table) {
            $table->bigIncrements('soa_id');
            $table->bigInteger('soa_so_id')->unsigned();
            $table->foreign('soa_so_id', 'tbl_soa_so_id_foreign')->references('so_id')->on('sales_order');
            $table->bigInteger('soa_created_by');
            $table->dateTime('soa_created_on');
            $table->bigInteger('soa_updated_by')->nullable();
            $table->dateTime('soa_updated_on')->nullable();
            $table->string('soa_deleted_reason', 255)->nullable();
            $table->bigInteger('soa_deleted_by')->nullable();
            $table->dateTime('soa_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_archive');
    }
}
