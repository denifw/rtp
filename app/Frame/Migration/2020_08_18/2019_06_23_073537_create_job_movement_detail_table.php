<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobMovementDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_movement_detail', function (Blueprint $table) {
            $table->bigIncrements('jmd_id');
            $table->bigInteger('jmd_jm_id')->unsigned();
            $table->foreign('jmd_jm_id', 'tbl_jmd_jm_id_foreign')->references('jm_id')->on('job_movement');
            $table->bigInteger('jmd_jid_id')->unsigned();
            $table->foreign('jmd_jid_id', 'tbl_jmd_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->float('jmd_quantity');
            $table->bigInteger('jmd_uom_id')->unsigned();
            $table->foreign('jmd_uom_id', 'tbl_jmd_uom_id_foreign')->references('uom_id')->on('unit');
            $table->bigInteger('jmd_whs_id')->unsigned();
            $table->foreign('jmd_whs_id', 'tbl_jmd_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
            $table->bigInteger('jmd_jis_id')->unsigned()->nullable();
            $table->foreign('jmd_jis_id', 'tbl_jmd_jis_id_foreign')->references('jis_id')->on('job_inbound_stock');
            $table->bigInteger('jmd_jid_new_id')->unsigned()->nullable();
            $table->foreign('jmd_jid_new_id', 'tbl_jmd_jid_new_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->bigInteger('jmd_jis_new_id')->unsigned()->nullable();
            $table->foreign('jmd_jis_new_id', 'tbl_jmd_jis_new_id_foreign')->references('jis_id')->on('job_inbound_stock');
            $table->bigInteger('jmd_gdt_id')->unsigned()->nullable();
            $table->foreign('jmd_gdt_id', 'tbl_jmd_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->string('jmd_gdt_remark', '255')->nullable();
            $table->bigInteger('jmd_gcd_id')->unsigned()->nullable();
            $table->foreign('jmd_gcd_id', 'tbl_jmd_gcd_id_foreign')->references('gcd_id')->on('goods_cause_damage');
            $table->string('jmd_gcd_remark', '255')->nullable();            $table->bigInteger('jmd_created_by');
            $table->dateTime('jmd_created_on');
            $table->bigInteger('jmd_updated_by')->nullable();
            $table->dateTime('jmd_updated_on')->nullable();
            $table->bigInteger('jmd_deleted_by')->nullable();
            $table->dateTime('jmd_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_movement_detail');
    }
}
