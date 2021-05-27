<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDttTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_template_type', function (Blueprint $table) {
            $table->dropUnique('tbl_dtt_description_unique');
            $table->string('dtt_code', 125)->nullable();
        });
        $query = 'SELECT dtt_id, dtt_description FROM document_template_type';
        $sqlResults = \Illuminate\Support\Facades\DB::select($query);
        foreach ($sqlResults as $row) {
            $code = mb_strtolower($row->dtt_description);
            $code = str_replace(' ', '', $code);
            DB::table('document_template_type')
                ->where('dtt_id', $row->dtt_id)
                ->update(['dtt_code' => $code]);
        }
        Schema::table('document_template_type', function (Blueprint $table) {
            $table->string('dtt_code', 125)->nullable(false)->change();
            $table->unique('dtt_code', 'tbl_dtt_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_template_type', function (Blueprint $table) {
            $table->dropUnique('tbl_dtt_code_unique');
            $table->dropColumn('dtt_code');
            $table->unique('dtt_description', 'tbl_dtt_description_unique');
        });
    }
}
