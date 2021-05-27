<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInboundReceiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inbound_receive', function (Blueprint $table) {
            $table->bigIncrements('jir_id');
            $table->bigInteger('jir_ji_id')->unsigned();
            $table->foreign('jir_ji_id', 'tbl_jir_ji_id_foreign')->references('ji_id')->on('job_inbound');
            $table->bigInteger('jir_jog_id')->unsigned();
            $table->foreign('jir_jog_id', 'tbl_jir_jog_id_foreign')->references('jog_id')->on('job_goods');
            $table->float('jir_quantity');
            $table->float('jir_qty_damage');
            $table->bigInteger('jir_gdt_id')->unsigned()->nullable();
            $table->foreign('jir_gdt_id', 'tbl_jir_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->string('jir_gdt_remark', '255')->nullable();
            $table->bigInteger('jir_gcd_id')->unsigned()->nullable();
            $table->foreign('jir_gcd_id', 'tbl_jir_gcd_id_foreign')->references('gcd_id')->on('goods_cause_damage');
            $table->string('jir_gcd_remark', '255')->nullable();
            $table->bigInteger('jir_created_by');
            $table->dateTime('jir_created_on');
            $table->bigInteger('jir_updated_by')->nullable();
            $table->dateTime('jir_updated_on')->nullable();
            $table->bigInteger('jir_deleted_by')->nullable();
            $table->dateTime('jir_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inbound_receive');
    }
}
