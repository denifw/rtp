<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSnTblUpdateConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_number', function (Blueprint $table) {
            $table->char('sn_relation', 1)->nullable();
            $table->char('sn_format', 1)->nullable();
        });
        $query = 'SELECT sn_id, sn_rel_id, sn_of_id, sn_srv_id, sn_srt_id FROM serial_number';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $relation = 'N';
            if ($row->sn_rel_id !== null) {
                $relation = 'Y';
            }
            $format = 'A';
            DB::table('serial_number')->where('sn_id', $row->sn_id)->update([
                'sn_relation' => $relation,
                'sn_format' => $format,
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
        Schema::table('serial_number', function (Blueprint $table) {
            $table->dropColumn('sn_relation');
            $table->dropColumn('sn_format');
        });
    }
}
