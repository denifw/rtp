<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobBundlingMaterialAddJidId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_bundling_material', function (Blueprint $table) {
            $table->bigInteger('jbm_jid_id')->unsigned()->nullable();
            $table->foreign('jbm_jid_id', 'tbl_jbm_jid_id_foreign')->references('jid_id')->on('job_inbound_detail');
            $table->bigInteger('jbm_gd_id')->unsigned()->nullable();
            $table->foreign('jbm_gd_id', 'tbl_jbm_gd_id_foreign')->references('gd_id')->on('goods');
            $table->bigInteger('jbm_gdu_id')->unsigned()->nullable();
            $table->foreign('jbm_gdu_id', 'tbl_jbm_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
            $table->bigInteger('jbm_gdt_id')->unsigned()->nullable();
            $table->foreign('jbm_gdt_id', 'tbl_jbm_gdt_id_foreign')->references('gdt_id')->on('goods_damage_type');
            $table->string('jbm_gdt_remark', '255')->nullable();
            $table->bigInteger('jbm_gcd_id')->unsigned()->nullable();
            $table->foreign('jbm_gcd_id', 'tbl_jbm_gcd_id_foreign')->references('gcd_id')->on('goods_cause_damage');
            $table->string('jbm_gcd_remark', '255')->nullable();
            $table->string('jbm_packing_number', 255)->nullable();
            $table->date('jbm_expired_date')->nullable();
            $table->float('jbm_length')->nullable();
            $table->float('jbm_width')->nullable();
            $table->float('jbm_height')->nullable();
            $table->float('jbm_volume')->nullable();
            $table->float('jbm_weight')->nullable();
            $table->char('jbm_stored', 1)->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_bundling_material', function (Blueprint $table) {
            $table->dropForeign('tbl_jbm_jid_id_foreign');
            $table->dropColumn('jbm_jid_id');
            $table->dropForeign('tbl_jbm_gd_id_foreign');
            $table->dropColumn('jbm_gd_id');
            $table->dropForeign('tbl_jbm_gdu_id_foreign');
            $table->dropColumn('jbm_gdu_id');
            $table->dropForeign('tbl_jbm_gdt_id_foreign');
            $table->dropColumn('jbm_gdt_id');
            $table->dropForeign('tbl_jbm_gcd_id_foreign');
            $table->dropColumn('jbm_gcd_id');
            $table->dropColumn('jbm_gdt_remark');
            $table->dropColumn('jbm_gcd_remark');
            $table->dropColumn('jbm_packing_number');
            $table->dropColumn('jbm_expired_date');
            $table->dropColumn('jbm_length');
            $table->dropColumn('jbm_width');
            $table->dropColumn('jbm_height');
            $table->dropColumn('jbm_volume');
            $table->dropColumn('jbm_weight');
        });
    }
}
