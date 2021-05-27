<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUnitTableRemoveSsId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('unit')->where('uom_id', 1)->update([
            'uom_code' => 'M3',
            'uom_name' => 'Meter Cubic'
        ]);
        Schema::table('unit', function (Blueprint $table) {
            $table->dropUnique('tbl_uom_ss_id_code_unique');
            $table->dropForeign('tbl_uom_ss_id_foreign');
            $table->dropColumn('uom_ss_id');
            $table->unique('uom_code', 'tbl_uom_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
