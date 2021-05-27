<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobOutboundDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->float('jod_qty_loaded')->nullable();
        });
        $query = 'SELECT jod.jod_id, jod.jod_quantity
            FROM job_outbound_detail as jod INNER JOIN
              job_outbound as job ON jod.jod_job_id = job.job_id
            WHERE (job.job_end_load_on IS NOT NULL) AND (jod.jod_deleted_on IS NULL)';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_outbound_detail')
                ->where('jod_id', $row->jod_id)
                ->update(['jod_qty_loaded' => $row->jod_quantity]);

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_outbound_detail', function (Blueprint $table) {
            $table->dropColumn('jod_qty_loaded');
        });
    }
}
