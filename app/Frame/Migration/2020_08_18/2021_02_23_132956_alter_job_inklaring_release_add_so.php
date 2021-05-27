<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobInklaringReleaseAddSo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inklaring_release', function (Blueprint $table) {
            $table->dropForeign('tbl_jikr_uom_id_foreign');
            $table->dropForeign('tbl_jikr_ct_id_foreign');
            $table->dropColumn('jikr_uom_id');
            $table->dropColumn('jikr_ct_id');
            $table->dropColumn('jikr_load_by');
            $table->bigInteger('jikr_soc_id')->unsigned()->nullable();
            $table->foreign('jikr_soc_id', 'tbl_jikr_soc_id_fkey')->references('soc_id')->on('sales_order_container');
            $table->bigInteger('jikr_sog_id')->unsigned()->nullable();
            $table->foreign('jikr_sog_id', 'tbl_jikr_sog_id_fkey')->references('sog_id')->on('sales_order_goods');
            $table->string('jikr_truck_number', 255)->nullable(true)->change();
            $table->date('jikr_load_date')->nullable(true)->change();
            $table->time('jikr_load_time')->nullable(true)->change();
            $table->bigInteger('jikr_load_by')->nullable(true)->change();
        });
        $query = 'SELECT jikr.jikr_id, srt.srt_container, soc.soc_id, sog.sog_id
            FROM job_inklaring_release as jikr
            INNER JOIN job_inklaring as jik ON jikr.jikr_jik_id = jik.jik_id
            INNER JOIN job_order as jo ON jik.jik_jo_id = jo.jo_id
            INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
            LEFT OUTER JOIN sales_order_container as soc ON jikr.jikr_joc_id = soc.soc_joc_id
            LEFT OUTER JOIN sales_order_goods as sog ON jikr.jikr_jog_id = sog.sog_jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inklaring_release')
                ->where('jikr_id', $row->jikr_id)
                ->update([
                    'jikr_soc_id' => $row->soc_id,
                    'jikr_sog_id' => $row->sog_id,
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
//        Schema::table('job_inklaring_release', function (Blueprint $table) {
//            $table->dropForeign('tbl_jikr_soc_id_fkey');
//            $table->dropForeign('tbl_jikr_sog_id_fkey');
//            $table->dropColumn('jikr_soc_id');
//            $table->dropColumn('jikr_sog_id');
//            $table->bigInteger('jikr_uom_id')->unsigned()->nullable();
//            $table->foreign('jikr_uom_id', 'tbl_jikr_uom_id_foreign')->references('uom_id')->on('unit');
//            $table->bigInteger('jikr_ct_id')->unsigned()->nullable();
//            $table->foreign('jikr_ct_id', 'tbl_jikr_ct_id_foreign')->references('ct_id')->on('container');
//            $table->bigInteger('jikr_load_by');
//        });
    }
}
