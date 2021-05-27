<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterJobGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_goods', function (Blueprint $table) {
            $table->string('jog_serial_number', 255)->nullable()->change();
            $table->renameColumn('jog_net_weight', 'jog_weight');
            $table->dropColumn('jog_gross_weight');
            $table->bigInteger('jog_gdu_id')->unsigned()->nullable();
            $table->foreign('jog_gdu_id', 'tbl_jog_gdu_id_foreign')->references('gdu_id')->on('goods_unit');
        });
        $query = 'SELECT jog.jog_id, gdu.gdu_id, gdu.gdu_gd_id
                    FROM job_goods as jog INNER JOIN
                    goods_unit as gdu ON gdu.gdu_gd_id = jog.jog_gd_id';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_goods')
                ->where('jog_id', $row->jog_id)
                ->update([
                    'jog_gdu_id' => $row->gdu_id,
                    'jog_uom_id' => null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $query = 'SELECT jog.jog_id, gdu.gdu_id, gdu.gdu_uom_id
                FROM job_goods as jog INNER JOIN
                 goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_goods')
                ->where('jog_id', $row->jog_id)
                ->update([
                    'jog_uom_id' => $row->gdu_uom_id,
                    'jog_gdu_id' => null,
                ]);
        }
        Schema::table('job_goods', function (Blueprint $table) {
            $table->string('jog_serial_number', 255)->nullable()->change();
            $table->renameColumn('jog_weight', 'jog_net_weight');
            $table->float('jog_gross_weight')->nullable();
            $table->dropForeign('tbl_jog_gdu_id_foreign');
            $table->dropColumn('jog_gdu_id');
        });
    }
}
