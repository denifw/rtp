<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGdAddWaranty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->char('gd_waranty', 1)->nullable();
        });
        $query = 'SELECT gd_id
                    FROM goods ';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            DB::table('goods')
                ->where('gd_id', $row->gd_id)
                ->update([
                    'gd_waranty' => 'N',
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
            $table->dropColumn('gd_waranty');
        });
    }
}
