<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGoodsSetSn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('gd_prefix_sn');
            $table->renameColumn('gd_pick_pack', 'gd_bundling');
            $table->char('gd_sn', 1)->nullable();
            $table->char('gd_receive_sn', 1)->nullable();
            $table->char('gd_packing', 1)->nullable();
            $table->char('gd_tonnage', 1)->nullable();
            $table->char('gd_tonnage_dm', 1)->nullable();
            $table->float('gd_min_tonnage')->nullable();
            $table->float('gd_max_tonnage')->nullable();
            $table->char('gd_cbm', 1)->nullable();
            $table->char('gd_cbm_dm', 1)->nullable();
            $table->float('gd_min_cbm')->nullable();
            $table->float('gd_max_cbm')->nullable();
            $table->char('gd_expired', 1)->nullable();
        });
        $query = 'SELECT gd_id, gd_ss_id, gd_unique_sn
                FROM goods ';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            if ($row->gd_ss_id === 2) {
                $data = [
                    'gd_sn' => 'N',
                    'gd_receive_sn' => 'N',
                    'gd_generate_sn' => 'N',
                    'gd_packing' => 'N',
                    'gd_tonnage' => 'N',
                    'gd_tonnage_dm' => 'Y',
                    'gd_cbm_dm' => 'N',
                    'gd_cbm' => 'N',
                    'gd_expired' => 'N',
                ];
            } else if ($row->gd_ss_id === 3) {
                $data = [
                    'gd_sn' => 'N',
                    'gd_receive_sn' => 'N',
                    'gd_generate_sn' => 'N',
                    'gd_packing' => 'N',
                    'gd_tonnage' => 'N',
                    'gd_cbm' => 'N',
                    'gd_tonnage_dm' => 'N',
                    'gd_cbm_dm' => 'N',
                    'gd_expired' => 'N',
                ];
            } else if ($row->gd_ss_id === 4) {
                if ($row->gd_unique_sn === 'Y') {
                    $data = [
                        'gd_sn' => 'Y',
                        'gd_receive_sn' => 'N',
                        'gd_generate_sn' => 'N',
                        'gd_packing' => 'N',
                        'gd_tonnage' => 'N',
                        'gd_cbm' => 'N',
                        'gd_tonnage_dm' => 'N',
                        'gd_cbm_dm' => 'N',
                        'gd_expired' => 'N',
                    ];
                } else {
                    $data = [
                        'gd_sn' => 'N',
                        'gd_receive_sn' => 'N',
                        'gd_generate_sn' => 'N',
                        'gd_packing' => 'N',
                        'gd_tonnage' => 'N',
                        'gd_cbm' => 'N',
                        'gd_tonnage_dm' => 'N',
                        'gd_cbm_dm' => 'N',
                        'gd_expired' => 'N',
                    ];
                }
            } else if ($row->gd_ss_id === 6) {
                if ($row->gd_unique_sn === 'Y') {
                    $data = [
                        'gd_sn' => 'Y',
                        'gd_receive_sn' => 'Y',
                        'gd_generate_sn' => 'Y',
                        'gd_packing' => 'Y',
                        'gd_tonnage' => 'Y',
                        'gd_cbm' => 'N',
                        'gd_tonnage_dm' => 'N',
                        'gd_cbm_dm' => 'N',
                        'gd_expired' => 'N',
                    ];
                } else {
                    $data = [
                        'gd_sn' => 'N',
                        'gd_receive_sn' => 'N',
                        'gd_generate_sn' => 'N',
                        'gd_packing' => 'N',
                        'gd_tonnage' => 'N',
                        'gd_cbm' => 'N',
                        'gd_tonnage_dm' => 'N',
                        'gd_cbm_dm' => 'N',
                        'gd_expired' => 'N',
                    ];
                }
            } else {
                $data = [
                    'gd_sn' => 'N',
                    'gd_receive_sn' => 'N',
                    'gd_generate_sn' => 'N',
                    'gd_packing' => 'N',
                    'gd_tonnage' => 'N',
                    'gd_cbm' => 'N',
                    'gd_tonnage_dm' => 'N',
                    'gd_cbm_dm' => 'N',
                    'gd_expired' => 'N',
                ];
            }

            DB::table('goods')
                ->where('gd_id', $row->gd_id)
                ->update($data);
        }
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('gd_unique_sn');
            $table->dropColumn('gd_mandatory_weight');
            $table->dropColumn('gd_mandatory_volume');
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
