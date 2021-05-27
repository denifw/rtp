<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterJobWarehouseDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # Job Inbound Damage
        Schema::table('job_inbound_damage', function (Blueprint $table) {
            $table->renameColumn('jidm_net_weight', 'jidm_weight');
            $table->dropColumn('jidm_gross_weight');
        });

        # Job Inbound Detail
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jid_uom_id_foreign');
            $table->dropColumn('jid_uom_id');
            $table->bigInteger('jid_gdu_id')->unsigned()->nullable();
            $table->foreign('jid_gdu_id', 'tbl_jid_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
        });
        $query = 'select jid.jid_id, jir.jir_id, jog.jog_gdu_id
                    FROM job_inbound_detail as jid INNER JOIN
                    job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                    job_goods as jog ON jog.jog_id = jir.jir_jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inbound_detail')
                ->where('jid_id', $row->jid_id)
                ->update([
                    'jid_gdu_id' => $row->jog_gdu_id
                ]);
        }
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->bigInteger('jid_gdu_id')->unsigned()->nullable(false)->change();
        });

        # Job Outbound Detail
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->bigInteger('jod_gdu_id')->unsigned()->nullable();
            $table->foreign('jod_gdu_id', 'tbl_jod_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
        });
        $query = 'SELECT jod.jod_id, jog.jog_id, jog.jog_gdu_id
                    FROM job_outbound_detail as jod INNER JOIN
                    job_goods as jog ON jod.jod_jog_id = jog.jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound_detail')
                ->where('jod_id', $row->jod_id)
                ->update([
                    'jod_gdu_id' => $row->jog_gdu_id
                ]);
        }
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jod_uom_id_foreign');
            $table->dropColumn('jod_uom_id');
            $table->bigInteger('jod_gdu_id')->unsigned()->nullable(false)->change();
        });
        
        # Job Movement Detail
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->bigInteger('jmd_gdu_id')->unsigned()->nullable();
            $table->foreign('jmd_gdu_id', 'tbl_jmd_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
        });
        $query = 'SELECT jmd.jmd_id, jid.jid_id, jid.jid_gdu_id from 
                    job_movement_detail as jmd INNER JOIN
                    job_inbound_detail as jid ON jid.jid_id = jmd.jmd_jid_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_movement_detail')
                ->where('jmd_id', $row->jmd_id)
                ->update([
                    'jmd_gdu_id' => $row->jid_gdu_id
                ]);
        }
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jmd_uom_id_foreign');
            $table->dropColumn('jmd_uom_id');
            $table->bigInteger('jmd_gdu_id')->unsigned()->nullable(false)->change();
        });

        # Job Adjustment Detail
        Schema::table('job_adjustment_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jad_uom_id_foreign');
            $table->dropColumn('jad_uom_id');
            $table->bigInteger('jad_gdu_id')->unsigned();
            $table->foreign('jad_gdu_id', 'tbl_jad_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        # Job Inbound Damage
        Schema::table('job_inbound_damage', function (Blueprint $table) {
            $table->renameColumn('jidm_weight', 'jidm_net_weight');
            $table->float('jidm_gross_weight')->nullable();
        });

        # Job Inbound Detail
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->bigInteger('jid_uom_id')->unsigned()->nullable();
            $table->foreign('jid_uom_id', 'tbl_jid_uom_id_foreign')->references('uom_id')->on('unit');
        });
        $query = 'select jid.jid_id, jid.jid_gdu_id, gdu.gdu_uom_id
                    FROM job_inbound_detail as jid INNER JOIN
                    goods_unit as gdu ON gdu.gdu_id = jid.jid_gdu_id';
        $sqlResults2 = DB::select($query);
        foreach ($sqlResults2 as $row) {
            DB::table('job_inbound_detail')
                ->where('jid_id', $row->jid_id)
                ->update([
                    'jid_uom_id' => $row->gdu_uom_id
                ]);
        }
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jid_gdu_id_foreign');
            $table->dropColumn('jid_gdu_id');
            $table->bigInteger('jid_uom_id')->unsigned()->nullable(false)->change();
        });

        # Job Outbound Detail
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->bigInteger('jod_uom_id')->unsigned()->nullable();
            $table->foreign('jod_uom_id', 'tbl_jod_uom_id_foreign')->references('uom_id')->on('unit');
        });
        $query = 'SELECT jod.jod_id, gdu.gdu_id, gdu.gdu_uom_id
                    FROM job_outbound_detail as jod INNER JOIN
                    goods_unit as gdu ON jod.jod_gdu_id = gdu.gdu_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound_detail')
                ->where('jod_id', $row->jod_id)
                ->update([
                    'jod_uom_id' => $row->gdu_uom_id
                ]);
        }
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jod_gdu_id_foreign');
            $table->dropColumn('jod_gdu_id');
            $table->bigInteger('jod_uom_id')->unsigned()->nullable(false)->change();
        });

        # Job Movement Detail
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->bigInteger('jmd_uom_id')->unsigned()->nullable();
            $table->foreign('jmd_uom_id', 'tbl_jmd_uom_id_foreign')->references('uom_id')->on('unit');
        });
        $query = 'SELECT jmd.jmd_id, gdu.gdu_id, gdu.gdu_uom_id
                    FROM job_movement_detail as jmd INNER JOIN
                    goods_unit as gdu ON gdu.gdu_id = jmd.jmd_gdu_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_movement_detail')
                ->where('jmd_id', $row->jmd_id)
                ->update([
                    'jmd_uom_id' => $row->gdu_uom_id
                ]);
        }
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jmd_gdu_id_foreign');
            $table->dropColumn('jmd_gdu_id');
            $table->bigInteger('jmd_uom_id')->unsigned()->nullable(false)->change();
        });
        # Job Adjustment Detail
        Schema::table('job_adjustment_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jad_gdu_id_foreign');
            $table->dropColumn('jad_gdu_id');
            $table->bigInteger('jad_uom_id')->unsigned();
            $table->foreign('jad_uom_id', 'tbl_jad_uom_id_foreign')->references('uom_id')->on('unit');
        });
    }
}
