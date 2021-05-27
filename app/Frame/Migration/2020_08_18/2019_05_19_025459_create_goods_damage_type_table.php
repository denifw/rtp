<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsDamageTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_damage_type', function (Blueprint $table) {
            $table->bigIncrements('gdt_id');
            $table->bigInteger('gdt_ss_id')->unsigned();
            $table->foreign('gdt_ss_id', 'tbl_gdt_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('gdt_code', 125);
            $table->string('gdt_description', 255);
            $table->char('gdt_active', 1)->default('Y');
            $table->bigInteger('gdt_created_by');
            $table->dateTime('gdt_created_on');
            $table->bigInteger('gdt_updated_by')->nullable();
            $table->dateTime('gdt_updated_on')->nullable();
            $table->bigInteger('gdt_deleted_by')->nullable();
            $table->dateTime('gdt_deleted_on')->nullable();
            $table->unique(['gdt_ss_id', 'gdt_code'], 'tbl_gdt_ss_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_damage_type');
    }
}
