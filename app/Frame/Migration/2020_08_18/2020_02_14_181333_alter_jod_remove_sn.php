<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJodRemoveSn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->bigInteger('jod_gd_id')->unsigned()->nullable();
            $table->foreign('jod_gd_id', 'tbl_jod_gd_id_foreign')->references('gd_id')->on('goods');
            $table->string('jod_lot_number', 255)->nullable();
            $table->dropColumn('jod_serial_number');
            $table->dropColumn('jod_packing_number');
        });
        $query = 'select jod.jod_id, jog.jog_gd_id
        FROM job_outbound_detail as jod INNER JOIN
        job_goods as jog ON jog.jog_id = jod.jod_jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound_detail')
                ->where('jod_id', $row->jod_id)
                ->update([
                    'jod_gd_id' => $row->jog_gd_id
                ]);
        }
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->bigInteger('jod_gd_id')->unsigned()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jod_gd_id_foreign');
            $table->dropColumn('jod_gd_id');
            $table->dropColumn('jod_lot_number');
            $table->string('jod_serial_number', 255)->nullable();
            $table->string('jod_packing_number', 255)->nullable();
        });
    }
}
