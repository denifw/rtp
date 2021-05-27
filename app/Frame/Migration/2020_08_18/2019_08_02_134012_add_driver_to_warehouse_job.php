<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDriverToWarehouseJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_inbound', function (Blueprint $table) {
            $table->string('ji_driver', 255)->nullable();
            $table->string('ji_driver_phone', 255)->nullable();
        });
        Schema::table('job_outbound', function (Blueprint $table) {
            $table->string('job_driver', 255)->nullable();
            $table->string('job_driver_phone', 255)->nullable();
        });
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->float('jmd_length')->nullable();
            $table->float('jmd_height')->nullable();
            $table->float('jmd_width')->nullable();
            $table->float('jmd_volume')->nullable();
            $table->float('jmd_weight')->nullable();
        });
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->float('jid_length')->nullable();
            $table->float('jid_height')->nullable();
            $table->float('jid_width')->nullable();
            $table->float('jid_volume')->nullable();
            $table->float('jid_weight')->nullable();
        });
        $query = 'SELECT jid.jid_id, jog.jog_length, jog.jog_width, jog.jog_height, jog.jog_volume, jog.jog_net_weight
                FROM job_inbound_detail as jid INNER JOIN
                job_inbound_receive as jir ON jid.jid_jir_id = jir.jir_id INNER JOIN
                job_goods as jog ON jir.jir_jog_id = jog.jog_id';
        $results = \Illuminate\Support\Facades\DB::select($query);
        foreach ($results as $row) {
            DB::table('job_inbound_detail')->where('jid_id', $row->jid_id)->update([
                'jid_length' => $row->jog_length,
                'jid_height' => $row->jog_height,
                'jid_width' => $row->jog_width,
                'jid_volume' => $row->jog_volume,
                'jid_weight' => $row->jog_net_weight
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
        Schema::table('job_inbound', function (Blueprint $table) {
            $table->dropColumn('ji_driver');
            $table->dropColumn('ji_driver_phone');
        });
        Schema::table('job_outbound', function (Blueprint $table) {
            $table->dropColumn('job_driver');
            $table->dropColumn('job_driver_phone');
        });
        Schema::table('job_movement_detail', function (Blueprint $table) {
            $table->dropColumn('jmd_length');
            $table->dropColumn('jmd_height');
            $table->dropColumn('jmd_width');
            $table->dropColumn('jmd_volume');
            $table->dropColumn('jmd_weight');
        });
        Schema::table('job_inbound_detail', function (Blueprint $table) {
            $table->dropColumn('jid_length');
            $table->dropColumn('jid_height');
            $table->dropColumn('jid_width');
            $table->dropColumn('jid_volume');
            $table->dropColumn('jid_weight');
        });
    }
}
