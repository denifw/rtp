<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGoodsUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('goods_unit');
        Schema::create('goods_unit', function (Blueprint $table) {
            $table->bigIncrements('gdu_id');
            $table->bigInteger('gdu_gd_id')->unsigned();
            $table->foreign('gdu_gd_id', 'tbl_gdu_gd_id_foreign')->references('gd_id')->on('goods');
            $table->float('gdu_quantity');
            $table->bigInteger('gdu_uom_id')->unsigned();
            $table->foreign('gdu_uom_id', 'tbl_gdu_uom_id_foreign')->references('uom_id')->on('unit');
            $table->float('gdu_qty_conversion');
            $table->float('gdu_length')->nullable();
            $table->float('gdu_width')->nullable();
            $table->float('gdu_height')->nullable();
            $table->float('gdu_volume')->nullable();
            $table->float('gdu_weight')->nullable();
            $table->char('gdu_active', 1)->default('Y');
            $table->bigInteger('gdu_created_by');
            $table->dateTime('gdu_created_on');
            $table->bigInteger('gdu_updated_by')->nullable();
            $table->dateTime('gdu_updated_on')->nullable();
            $table->bigInteger('gdu_deleted_by')->nullable();
            $table->dateTime('gdu_deleted_on')->nullable();
            $table->unique(['gdu_gd_id', 'gdu_uom_id'], 'tbl_gdu_gd_uom_id_unique');
        });
        $sqlResults = \Illuminate\Support\Facades\DB::select(
            'SELECT gd_id, gd_uom_id, gd_length, gd_height, gd_width, gd_volume, (CASE WHEN gd_net_weight IS NULL THEN gd_gross_weight ELSE gd_net_weight END) as gd_weight  
                    FROM goods 
                    ORDER BY gd_id'
        );
        foreach ($sqlResults as $row) {
            DB::table('goods_unit')->insert([
                'gdu_gd_id' => $row->gd_id,
                'gdu_quantity' => 1,
                'gdu_uom_id' => $row->gd_uom_id,
                'gdu_qty_conversion' => 1,
                'gdu_length' => $row->gd_length,
                'gdu_width' => $row->gd_width,
                'gdu_height' => $row->gd_height,
                'gdu_volume' => $row->gd_volume,
                'gdu_weight' => $row->gd_weight,
                'gdu_active' => 'Y',
                'gdu_created_on' => date('Y-m-d H:i:s'),
                'gdu_created_by' => 1
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
        //
    }
}
