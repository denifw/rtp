<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobOutboundAddSo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_outbound', function (Blueprint $table) {
            $table->bigInteger('job_so_id')->unsigned()->nullable();
            $table->foreign('job_so_id', 'tbl_job_so_id_fkey')->references('so_id')->on('sales_order');
            $table->bigInteger('job_soc_id')->unsigned()->nullable();
            $table->foreign('job_soc_id', 'tbl_job_soc_id_fkey')->references('soc_id')->on('sales_order_container');
        });

        $query = 'SELECT job.job_id, jo.jo_so_id
                FROM job_outbound as job
                INNER JOIN job_order aS jo ON job.job_jo_id = jo.jo_id
                WHERE jo.jo_so_id is not null';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound')
                ->where('job_id', $row->job_id)
                ->update([
                    'job_so_id' => $row->jo_so_id,
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
//        Schema::table('job_outbound', function (Blueprint $table) {
//            $table->dropForeign('tbl_job_so_id_fkey');
//            $table->dropForeign('tbl_job_soc_id_fkey');
//            $table->dropColumn('job_so_id');
//            $table->dropColumn('job_soc_id');
//        });
    }
}
