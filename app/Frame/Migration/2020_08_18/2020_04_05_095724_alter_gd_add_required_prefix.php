<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterGdAddRequiredPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->char('gd_prefix_sn', 1)->nullable();
        });
        $query = 'SELECT gd_id, gd_ss_id, gd_unique_sn
                    FROM goods ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $prefix = 'N';
            if ((int) $row->gd_ss_id === 4 && $row->gd_unique_sn === 'Y') {
                $prefix = 'Y';
            }
            DB::table('goods')
                ->where('gd_id', $row->gd_id)
                ->update([
                    'gd_prefix_sn' => $prefix,
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
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('gd_prefix_sn');
        });
    }
}
