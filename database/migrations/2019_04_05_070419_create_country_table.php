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
            $table->bigIncrements('cnt_id');
            $table->string('cnt_name', 125);
            $table->string('cnt_iso', 10);
            $table->char('cnt_active', 1)->default('Y');
            $table->bigInteger('cnt_created_by');
            $table->dateTime('cnt_created_on');
            $table->bigInteger('cnt_updated_by')->nullable();
            $table->dateTime('cnt_updated_on')->nullable();
            $table->bigInteger('cnt_deleted_by')->nullable();
            $table->dateTime('cnt_deleted_on')->nullable();
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
