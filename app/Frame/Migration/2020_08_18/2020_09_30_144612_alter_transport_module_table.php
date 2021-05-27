<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransportModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_module', function (Blueprint $table) {
            $table->string('tm_code',125)->nullable();
        });

        $query = 'SELECT tm_id, tm_name from transport_module';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            $code = \App\Frame\Formatter\StringFormatter::replaceSpecialCharacter($row->tm_name);
            DB::table('transport_module')
                ->where('tm_id', $row->tm_id)
                ->update([
                    'tm_code' => mb_strtolower($code)
                ]);
        }

        Schema::table('transport_module', function (Blueprint $table) {
            $table->string('tm_code',125)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_module', function (Blueprint $table) {
            $table->dropColumn('tm_code');
        });
    }
}
