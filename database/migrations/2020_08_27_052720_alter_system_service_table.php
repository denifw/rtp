<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSystemServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_service', function (Blueprint $table) {
            $table->dropUnique('tbl_ssr_srv_ss_id_unique');
            $table->bigInteger('ssr_srt_id')->unsigned()->nullable();
            $table->foreign('ssr_srt_id', 'tbl_ssr_srt_id_foreign')->references('srt_id')->on('service_term');
        });
        $sqlService = \Illuminate\Support\Facades\DB::select('SELECT ssr.ssr_id, ssr.ssr_ss_id, ssr.ssr_srv_id, ssr.ssr_active, srt.srt_id, srt.srt_name
                                                                        FROM system_service as ssr INNER JOIN
                                                                            service as srv ON ssr.ssr_srv_id = srv.srv_id INNER JOIN
                                                                            service_term as srt  ON srv.srv_id = srt.srt_srv_id
                                                                        ORDER BY ssr.ssr_ss_id, ssr.ssr_srv_id, ssr.ssr_id, srt.srt_id');
        $tempIds = [];
        foreach ($sqlService as $row) {
            if ((int)$row->srt_id !== 13 && (int)$row->srt_id !== 14) {
                if (in_array($row->ssr_id, $tempIds, true) === false) {
                    $tempIds[] = $row->ssr_id;
                    DB::table('system_service')->where('ssr_id', $row->ssr_id)->update([
                        'ssr_srt_id' => $row->srt_id,
                    ]);
                } else {
                    DB::table('system_service')->insert([
                        'ssr_ss_id' => $row->ssr_ss_id,
                        'ssr_srv_id' => $row->ssr_srv_id,
                        'ssr_srt_id' => $row->srt_id,
                        'ssr_active' => 'Y',
                        'ssr_created_on' => date('Y-m-d H:i:s'),
                        'ssr_created_by' => 1,
                    ]);
                }
            }
        }
        Schema::table('system_service', function (Blueprint $table) {
            $table->bigInteger('ssr_srt_id')->unsigned()->nullable(false)->change();
            $table->unique(['ssr_srt_id', 'ssr_ss_id'], 'tbl_ssr_srt_ss_id_unique');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
