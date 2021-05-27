<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit', function (Blueprint $table) {
            $table->bigIncrements('uom_id');
            $table->bigInteger('uom_ss_id')->unsigned();
            $table->foreign('uom_ss_id', 'tbl_uom_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('uom_name', 125);
            $table->string('uom_code', 50);
            $table->char('uom_active', 1)->default('Y');
            $table->bigInteger('uom_created_by');
            $table->dateTime('uom_created_on');
            $table->bigInteger('uom_updated_by')->nullable();
            $table->dateTime('uom_updated_on')->nullable();
            $table->bigInteger('uom_deleted_by')->nullable();
            $table->dateTime('uom_deleted_on')->nullable();
            $table->unique(['uom_code', 'uom_ss_id'], 'tbl_uom_ss_id_code_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => UnitSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit');
    }
}
