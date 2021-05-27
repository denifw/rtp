<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCctTableAddCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customs_clearance_type', function (Blueprint $table) {
            $table->string('cct_code', 128)->nullable();
        });
        DB::table('customs_clearance_type')->where('cct_id', 1)->update([
            'cct_code' => 'RL',
        ]);
        DB::table('customs_clearance_type')->where('cct_id', 2)->update([
            'cct_code' => 'GL',
        ]);
        DB::table('customs_clearance_type')->where('cct_id', 3)->update([
            'cct_code' => 'YL',
        ]);
        DB::table('customs_clearance_type')->where('cct_id', 4)->update([
            'cct_code' => 'RH',
        ]);
        Schema::table('customs_clearance_type', function (Blueprint $table) {
            $table->string('cct_code', 128)->nullable(false)->change();
            $table->unique('cct_code', 'tbl_cct_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customs_clearance_type', function (Blueprint $table) {
            $table->dropUnique('tbl_cct_code_unique');
            $table->dropColumn('cct_code');
        });
    }
}
