<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJidAddLotNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # Job Inbound Detail
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->bigInteger('jid_gd_id')->unsigned()->nullable();
            $table->foreign('jid_gd_id', 'tbl_jid_gd_id_foreign')->references('gd_id')->on('goods');
            $table->string('jid_lot_number', 255)->nullable();
        });
        $query = 'select jid.jid_id, jog.jog_gd_id, jog.jog_production_number
                            FROM job_inbound_detail as jid INNER JOIN
                            job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                            job_goods as jog ON jog.jog_id = jir.jir_jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inbound_detail')
                ->where('jid_id', $row->jid_id)
                ->update([
                    'jid_lot_number' => $row->jog_production_number,
                    'jid_gd_id' => $row->jog_gd_id

                ]);
        }
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->bigInteger('jid_gd_id')->unsigned()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jid_gd_id_foreign');
            $table->dropColumn('jid_gd_id');
            $table->dropColumn('jid_lot_number');
        });
    }
}
