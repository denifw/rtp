<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service', function (Blueprint $table) {
            $table->string('srv_code', 125)->nullable();
            $table->dropUnique('tbl_srv_name_unique');
        });
        $sqlService = \Illuminate\Support\Facades\DB::select('select srv_id, srv_name from service;');
        foreach ($sqlService as $row) {
            $code = mb_strtolower(str_replace(' ', '', $row->srv_name));
            DB::table('service')->where('srv_id', $row->srv_id)->update([
                'srv_code' => $code,
            ]);
        }
        Schema::table('service', function (Blueprint $table) {
            $table->string('srv_code', 125)->nullable(false)->change();
            $table->unique('srv_code', 'tbl_srv_code_unique');
        });

        $sqlService = \Illuminate\Support\Facades\DB::select('select srt_id, srt_route from service_term;');
        foreach ($sqlService as $row) {
            $route = str_replace('/detail', '', $row->srt_route);
            DB::table('service_term')->where('srt_id', $row->srt_id)->update([
                'srt_route' => $route,
            ]);
        }
        Schema::table('service_term', function (Blueprint $table) {
            $table->dropUnique('tbl_srt_srv_id_name_unique');
            $table->unique('srt_route', 'tbl_srt_route_unique');
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
