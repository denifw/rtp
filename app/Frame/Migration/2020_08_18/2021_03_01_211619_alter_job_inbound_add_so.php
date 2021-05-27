<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobInboundAddSo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inbound', function (Blueprint $table) {
            $table->bigInteger('ji_so_id')->unsigned()->nullable();
            $table->foreign('ji_so_id', 'tbl_ji_so_id_fkey')->references('so_id')->on('sales_order');
            $table->bigInteger('ji_soc_id')->unsigned()->nullable();
            $table->foreign('ji_soc_id', 'tbl_ji_soc_id_fkey')->references('soc_id')->on('sales_order_container');
        });

        $query = 'SELECT ji.ji_id, jo.jo_so_id
                FROM job_inbound as ji
                INNER JOIN job_order aS jo ON ji.ji_jo_id = jo.jo_id
                WHERE jo.jo_so_id is not null';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inbound')
                ->where('ji_id', $row->ji_id)
                ->update([
                    'ji_so_id' => $row->jo_so_id,
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
//        Schema::table('job_inbound', function (Blueprint $table) {
//            $table->dropForeign('tbl_ji_so_id_fkey');
//            $table->dropForeign('tbl_ji_soc_id_fkey');
//            $table->dropColumn('ji_so_id');
//            $table->dropColumn('ji_soc_id');
//        });
    }
}
