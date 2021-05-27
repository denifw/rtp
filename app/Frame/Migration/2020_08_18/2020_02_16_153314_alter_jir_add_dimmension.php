<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJirAddDimmension extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # Job Inbound Detail
        Schema::table('job_inbound_receive', function (Blueprint $table) {
            $table->float('jir_length')->nullable();
            $table->float('jir_width')->nullable();
            $table->float('jir_height')->nullable();
            $table->float('jir_volume')->nullable();
            $table->float('jir_weight')->nullable();
        });
        $query = 'select jir.jir_id, jog.jog_length, jog.jog_height, jog.jog_width, jog.jog_volume, jog.jog_weight
                FROM job_inbound_receive as jir INNER JOIN
                job_goods as jog ON jog.jog_id = jir.jir_jog_id';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('job_inbound_receive')
                ->where('jir_id', $row->jir_id)
                ->update([
                    'jir_length' => $row->jog_length,
                    'jir_height' => $row->jog_height,
                    'jir_width' => $row->jog_width,
                    'jir_volume' => $row->jog_volume,
                    'jir_weight' => $row->jog_weight,

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
        Schema::table('job_inbound_receive', function (Blueprint $table) {
            $table->dropColumn('jir_length');
            $table->dropColumn('jir_width');
            $table->dropColumn('jir_height');
            $table->dropColumn('jir_volume');
            $table->dropColumn('jir_weight');
        });
    }
}
