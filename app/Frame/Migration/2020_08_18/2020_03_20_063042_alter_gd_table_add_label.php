<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterGdTableAddLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->string('gd_barcode', 255)->nullable();
            $table->char('gd_multi_sn', 1)->nullable();
        });
        $query = 'SELECT gd_id, gd_sku, gd_unique_sn
                    FROM goods ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $multi = 'N';
            if ($row->gd_unique_sn === 'Y') {
                $multi = 'Y';
            }
            DB::table('goods')
                ->where('gd_id', $row->gd_id)
                ->update([
                    'gd_barcode' => $row->gd_sku,
                    'gd_multi_sn' => $multi,
                ]);
        }
        $query2 = 'select gd.gd_id, gd.gd_sku, gd.gd_name, gd.gd_br_id, br.br_name
                    from goods as gd INNER JOIN
                    brand as br on br.br_id = gd.gd_br_id
                    where gd.gd_rel_id = 15';
        $data = DB::select($query2);
        $brId = '';
        foreach ($data as $obj) {
            if (empty($brId) === true) {
                $brId = $obj->gd_br_id;
                DB::table('brand')
                    ->where('br_id', $brId)
                    ->update([
                        'br_name' => 'SEIA'
                    ]);
            }
            if ((int)$brId !== (int)$obj->gd_br_id) {
                DB::table('brand')
                    ->where('br_id', $obj->gd_br_id)
                    ->update([
                        'br_active' => 'N',
                        'br_deleted_on' => date('Y-m-d H:i:s'),
                        'br_deleted_by' => 1,
                    ]);
            }
            $gdName = $obj->gd_name;
            $brName = $obj->br_name;
            if(strpos(mb_strtolower($gdName), mb_strtolower($brName)) !== false) {
                $newName = $gdName;
            } else {
                $newName = $brName.' '. $gdName;
            }
            DB::table('goods')
                ->where('gd_id', $obj->gd_id)
                ->update([
                    'gd_name' => $newName,
                    'gd_br_id' => $brId,
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
            $table->dropColumn('gd_barcode');
            $table->dropColumn('gd_multi_sn');
        });
        
    }
}
