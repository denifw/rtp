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
            $table->uuid('uom_id')->primary();
            $table->uuid('uom_ss_id')->unsigned();
            $table->foreign('uom_ss_id', 'tbl_uom_ss_id_fkey')->references('ss_id')->on('system_setting');
            $table->string('uom_name', 125);
            $table->string('uom_code', 50);
            $table->char('uom_active', 1)->default('Y');
            $table->uuid('uom_created_by');
            $table->dateTime('uom_created_on');
            $table->uuid('uom_updated_by')->nullable();
            $table->dateTime('uom_updated_on')->nullable();
            $table->uuid('uom_deleted_by')->nullable();
            $table->dateTime('uom_deleted_on')->nullable();
            $table->string('uom_deleted_reason', 256)->nullable();
            $table->unique('uom_code', 'tbl_uom_code_unique');
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
