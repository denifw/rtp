<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockOpnameDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname_detail', function (Blueprint $table) {
            $table->bigIncrements('sod_id');
            $table->bigInteger('sod_sop_id')->unsigned();
            $table->foreign('sod_sop_id', 'tbl_sod_sop_id_foreign')->references('sop_id')->on('stock_opname');
            $table->bigInteger('sod_jid_id')->unsigned();
            $table->foreign('sod_jid_id', 'tbl_sod_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->float('sod_quantity');
            $table->bigInteger('sod_uom_id')->unsigned();
            $table->foreign('sod_uom_id', 'tbl_sod_uom_id_foreign')->references('uom_id')->on('unit');
            $table->string('sod_remark', 255)->nullable();
            $table->bigInteger('sod_created_by');
            $table->dateTime('sod_created_on');
            $table->bigInteger('sod_updated_by')->nullable();
            $table->dateTime('sod_updated_on')->nullable();
            $table->bigInteger('sod_deleted_by')->nullable();
            $table->dateTime('sod_deleted_on')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_opname_detail');
    }
}
