<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockOpnameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->bigIncrements('sop_id');
            $table->bigInteger('sop_jo_id')->unsigned();
            $table->foreign('sop_jo_id', 'tbl_sop_jo_id_foreign')->references('jo_id')->on('job_order');
            $table->bigInteger('sop_wh_id')->unsigned();
            $table->foreign('sop_wh_id', 'tbl_sop_wh_id_foreign')->references('wh_id')->on('warehouse');
            $table->date('sop_date');
            $table->time('sop_time');
            $table->dateTime('sop_start_on')->nullable();
            $table->dateTime('sop_end_on')->nullable();
            $table->bigInteger('sop_created_by');
            $table->dateTime('sop_created_on');
            $table->bigInteger('sop_updated_by')->nullable();
            $table->dateTime('sop_updated_on')->nullable();
            $table->bigInteger('sop_deleted_by')->nullable();
            $table->dateTime('sop_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_opname');
    }
}
