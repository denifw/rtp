<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tax')->truncate();
        Schema::table('tax', function (Blueprint $table) {
            $table->bigInteger('tax_ss_id')->unsigned();
            $table->foreign('tax_ss_id', 'tbl_tax_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->dropUnique('tbl_tax_code_unique');
            $table->renameColumn('tax_code', 'tax_name');
            $table->dropColumn('tax_percent');
            $table->unique(['tax_ss_id', 'tax_name'], 'tbl_tax_ss_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax', function (Blueprint $table) {
            $table->dropForeign('tbl_tax_ss_id_foreign');
            $table->dropUnique('tbl_tax_ss_name_unique');
            $table->dropColumn('tax_ss_id');
            $table->renameColumn('tax_name', 'tax_code');
            $table->float('tax_percent')->nullable();
            $table->unique('tax_code', 'tbl_tax_code_unique');
        });
    }
}
