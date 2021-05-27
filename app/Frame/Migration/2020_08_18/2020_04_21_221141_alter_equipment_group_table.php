<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEquipmentGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_group', function (Blueprint $table) {
            $table->dropColumn('eg_length');
            $table->dropColumn('eg_width');
            $table->dropColumn('eg_height');
            $table->dropColumn('eg_net_weight');
            $table->dropColumn('eg_gross_weight');
            $table->dropColumn('eg_volume');
            $table->string('eg_code', 25);
            $table->unique('eg_code', 'eg_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_group', function (Blueprint $table) {
            $table->dropUnique('eg_code_unique');
            $table->dropColumn('eg_code');
            $table->float('eg_length')->nullable();
            $table->float('eg_width')->nullable();
            $table->float('eg_height')->nullable();
            $table->float('eg_net_weight')->nullable();
            $table->float('eg_gross_weight')->nullable();
            $table->float('eg_volume')->nullable();
        });
    }
}
