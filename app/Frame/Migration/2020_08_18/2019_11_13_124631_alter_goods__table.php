<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('gd_stackable');
            $table->dropColumn('gd_stackable_amount');
            $table->dropColumn('gd_minimum_temperature');
            $table->dropColumn('gd_maximum_temperature');
            $table->dropColumn('gd_minimum_stock');
            $table->dropColumn('gd_maximum_stock');
            $table->dropColumn('gd_length');
            $table->dropColumn('gd_width');
            $table->dropColumn('gd_height');
            $table->dropColumn('gd_volume');
            $table->dropColumn('gd_net_weight');
            $table->dropColumn('gd_gross_weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->char('gd_stackable', 1)->nullable();
            $table->bigInteger('gd_stackable_amount')->nullable();
            $table->float('gd_minimum_temperature')->nullable();
            $table->float('gd_maximum_temperature')->nullable();
            $table->float('gd_minimum_stock')->nullable();
            $table->float('gd_maximum_stock')->nullable();
            $table->float('gd_length')->nullable();
            $table->float('gd_width')->nullable();
            $table->float('gd_height')->nullable();
            $table->float('gd_volume')->nullable();
            $table->float('gd_net_weight')->nullable();
            $table->float('gd_gross_weight')->nullable();
        });
    }
}
