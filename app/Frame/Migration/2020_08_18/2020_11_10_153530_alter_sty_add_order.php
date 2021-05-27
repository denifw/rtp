<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStyAddOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_type', function (Blueprint $table) {
            $table->integer('sty_order')->nullable();
            $table->string('sty_label_type', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('system_type', function (Blueprint $table) {
//            $table->dropColumn('sty_order');
//        });
    }
}
