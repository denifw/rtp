<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInboundDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inbound_detail', function (Blueprint $table) {
            $table->bigIncrements('jid_id');
            $table->bigInteger('jid_ji_id')->unsigned();
            $table->foreign('jid_ji_id', 'tbl_jid_ji_id_foreign')->references('ji_id')->on('job_inbound');
            $table->bigInteger('jid_jir_id')->unsigned();
            $table->foreign('jid_jir_id', 'tbl_jid_jir_id_foreign')->references('jir_id')->on('job_inbound_receive');
            $table->bigInteger('jid_whs_id')->unsigned();
            $table->foreign('jid_whs_id', 'tbl_jid_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
            $table->float('jid_quantity');
            $table->bigInteger('jid_uom_id')->unsigned();
            $table->foreign('jid_uom_id', 'tbl_jid_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jid_gdt_id')->unsigned()->nullable();
            $table->foreign('jid_gdt_id', 'tbl_jid_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->string('jid_gdt_remark', '255')->nullable();
            $table->bigInteger('jid_gcd_id')->unsigned()->nullable();
            $table->foreign('jid_gcd_id', 'tbl_jid_gcd_id_foreign')->references('gcd_id')->on('goods_cause_damage');
            $table->string('jid_gcd_remark', '255')->nullable();
            $table->char('jid_adjustment', 1);
            $table->bigInteger('jid_created_by');
            $table->dateTime('jid_created_on');
            $table->bigInteger('jid_updated_by')->nullable();
            $table->dateTime('jid_updated_on')->nullable();
            $table->bigInteger('jid_deleted_by')->nullable();
            $table->dateTime('jid_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inbound_detail');
    }
}
