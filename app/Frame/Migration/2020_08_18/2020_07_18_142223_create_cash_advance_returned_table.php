<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceReturnedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_returned', function (Blueprint $table) {
            $table->bigIncrements('cart_id');
            $table->bigInteger('cart_ca_id')->unsigned();
            $table->foreign('cart_ca_id', 'tbl_cart_ca_id_foreign')->references('ca_id')->on('cash_advance');
            $table->string('cart_reject_reason', 255)->nullable();
            $table->bigInteger('cart_created_by');
            $table->dateTime('cart_created_on');
            $table->bigInteger('cart_updated_by')->nullable();
            $table->dateTime('cart_updated_on')->nullable();
            $table->bigInteger('cart_deleted_by')->nullable();
            $table->dateTime('cart_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance_returned');
    }
}
