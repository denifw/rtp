<?php

use App\Frame\Formatter\StringFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCnTableAddCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('container', function (Blueprint $table) {
            $table->string('ct_code', 128)->nullable();
        });
        $query = 'SELECT ct_id, ct_name FROM container';
        $sqlResults = DB::select($query);
        foreach ($sqlResults as $row) {
            $code = StringFormatter::replaceSpecialCharacter($row->ct_name);
            DB::table('container')->where('ct_id', $row->ct_id)->update([
                'ct_code' => $code,
            ]);
        }
        Schema::table('container', function (Blueprint $table) {
            $table->string('ct_code', 128)->nullable(false)->change();
            $table->unique('ct_code', 'tbl_ct_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('container', function (Blueprint $table) {
            $table->dropUnique('tbl_ct_code_unique');
            $table->dropColumn('ct_code');
        });
    }
}
