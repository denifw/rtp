<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country', function (Blueprint $table) {
            $table->uuid('cnt_id')->primary();
            $table->string('cnt_name', 128);
            $table->string('cnt_iso', 16);
            $table->char('cnt_active', 1)->default('Y');
            $table->uuid('cnt_created_by');
            $table->dateTime('cnt_created_on');
            $table->uuid('cnt_updated_by')->nullable();
            $table->dateTime('cnt_updated_on')->nullable();
            $table->uuid('cnt_deleted_by')->nullable();
            $table->dateTime('cnt_deleted_on')->nullable();
            $table->string('cnt_deleted_reason', 256)->nullable();
            $table->unique('cnt_iso', 'tbl_cnt_iso_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => CountrySeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country');
    }
}
