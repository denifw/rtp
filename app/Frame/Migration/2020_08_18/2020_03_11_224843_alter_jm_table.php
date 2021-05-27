<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterJmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_movement', function (Blueprint $table) {
            $table->bigInteger('jm_new_whs_id')->unsigned()->nullable();
            $table->foreign('jm_new_whs_id', 'tbl_jm_new_whs_id_foreign')->references('whs_id')->on('warehouse_storage');
        });
        $query = 'select jmd_jm_id, jmd_whs_id from job_movement_detail';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_movement')
            ->where('jm_id', $row->jmd_jm_id)
            ->update([
                'jm_new_whs_id' => $row->jmd_whs_id,
            ]);
        }
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->dropForeign('tbl_jmd_whs_id_foreign');
            $table->dropColumn('jmd_whs_id');
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
