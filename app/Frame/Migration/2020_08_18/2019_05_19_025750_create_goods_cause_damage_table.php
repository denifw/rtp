<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsCauseDamageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_cause_damage', function (Blueprint $table) {
            $table->bigIncrements('gcd_id');
            $table->bigInteger('gcd_ss_id')->unsigned();
            $table->foreign('gcd_ss_id', 'tbl_gcd_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('gcd_code', 125);
            $table->string('gcd_description', 255);
            $table->char('gcd_active', 1)->default('Y');
            $table->bigInteger('gcd_created_by');
            $table->dateTime('gcd_created_on');
            $table->bigInteger('gcd_updated_by')->nullable();
            $table->dateTime('gcd_updated_on')->nullable();
            $table->bigInteger('gcd_deleted_by')->nullable();
            $table->dateTime('gcd_deleted_on')->nullable();
            $table->unique(['gcd_ss_id', 'gcd_code'], 'tbl_gcd_ss_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_cause_damage');
    }
}
