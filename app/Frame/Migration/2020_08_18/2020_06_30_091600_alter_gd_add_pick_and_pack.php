<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGdAddPickAndPack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->char('gd_pick_pack', 1)->nullable();
            $table->char('gd_generate_sn', 1)->nullable();
            $table->char('gd_mandatory_weight', 1)->nullable();
            $table->char('gd_mandatory_volume', 1)->nullable();
            $table->renameColumn('gd_waranty', 'gd_warranty');
        });
        $query = 'SELECT gd_id, gd_ss_id
                    FROM goods ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $weight = 'N';
            if ((int)$row->gd_ss_id === 2) {
                $weight = 'Y';
            }
            DB::table('goods')
                ->where('gd_id', $row->gd_id)
                ->update([
                    'gd_pick_pack' => 'N',
                    'gd_generate_sn' => 'N',
                    'gd_mandatory_weight' => $weight,
                    'gd_mandatory_volume' => 'N',
                ]);
        }
        Schema::table('goods_prefix', function (Blueprint $table) {
            $table->char('gpf_yearly', 1)->nullable();
            $table->char('gpf_monthly', 1)->nullable();
            $table->integer('gpf_length')->nullable();
        });
        $query = 'SELECT gpf_id
                    FROM goods_prefix ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('goods_prefix')
                ->where('gpf_id', $row->gpf_id)
                ->update([
                    'gpf_yearly' => 'Y',
                    'gpf_monthly' => 'Y',
                    'gpf_length' => 9,
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
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('gd_pick_pack');
            $table->dropColumn('gd_generate_sn');
            $table->dropColumn('gd_mandatory_weight');
            $table->dropColumn('gd_mandatory_volume');
            $table->renameColumn('gd_warranty', 'gd_waranty');
        });
        Schema::table('goods_prefix', function (Blueprint $table) {
            $table->dropColumn('gpf_yearly');
            $table->dropColumn('gpf_monthly');
            $table->dropColumn('gpf_length');
        });
    }
}
