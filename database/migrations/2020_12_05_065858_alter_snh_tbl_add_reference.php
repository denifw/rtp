<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSnhTblAddReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_history', function (Blueprint $table) {
            $table->dropUnique('tbl_sh_sn_id_year_month_number_unique');
            $table->bigInteger('sh_rel_id')->unsigned()->nullable();
            $table->foreign('sh_rel_id', 'tbl_sh_rel_id_fkey')->references('rel_id')->on('relation');
        });
        $query = 'SELECT sh.sh_id, sh.sh_sn_id, sn.sn_rel_id
                FROM serial_history as sh INNER JOIN
                    serial_number as sn ON sh.sh_sn_id = sn.sn_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('serial_history')->where('sh_id', $row->sh_id)->update([
                'sh_rel_id' => $row->sn_rel_id,
            ]);
        }
        Schema::table('serial_history', function (Blueprint $table) {
            $table->unique(['sh_sn_id', 'sh_rel_id', 'sh_year', 'sh_month', 'sh_number'], 'tbl_sh_sn_rel_year_month_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serial_history', function (Blueprint $table) {
            $table->dropForeign('tbl_sh_rel_id_fkey');
            $table->dropUnique('tbl_sh_sn_rel_year_month_number_unique');
            $table->dropColumn('sh_rel_id');
            $table->unique(['sh_sn_id', 'sh_year', 'sh_month', 'sh_number'], 'tbl_sh_sn_id_year_month_number_unique');
        });
    }
}
