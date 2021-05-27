<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobInboundDamageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_inbound_damage', function (Blueprint $table) {
            $table->bigIncrements('jidm_id');
            $table->bigInteger('jidm_jir_id')->unsigned();
            $table->foreign('jidm_jir_id', 'tbl_jidm_jir_id_foreign')->references('jir_id')->on('job_inbound_receive');
            $table->float('jidm_quantity')->nullable();
            $table->float('jidm_length')->nullable();
            $table->float('jidm_width')->nullable();
            $table->float('jidm_height')->nullable();
            $table->float('jidm_gross_weight')->nullable();
            $table->float('jidm_net_weight')->nullable();
            $table->float('jidm_volume')->nullable();
            $table->bigInteger('jidm_gdt_id')->unsigned();
            $table->foreign('jidm_gdt_id', 'tbl_jidm_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->string('jidm_gdt_remark', '255')->nullable();
            $table->bigInteger('jidm_gcd_id')->unsigned();
            $table->foreign('jidm_gcd_id', 'tbl_jidm_gcd_id_foreign')->references('gcd_id')->on('goods_cause_damage');
            $table->string('jidm_gcd_remark', '255')->nullable();
            $table->char('jidm_stored', 1)->nullable();
            $table->bigInteger('jidm_created_by');
            $table->dateTime('jidm_created_on');
            $table->bigInteger('jidm_updated_by')->nullable();
            $table->dateTime('jidm_updated_on')->nullable();
            $table->bigInteger('jidm_deleted_by')->nullable();
            $table->dateTime('jidm_deleted_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_inbound_damage');
    }
}
