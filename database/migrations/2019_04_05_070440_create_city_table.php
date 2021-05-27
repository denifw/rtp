<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city', function (Blueprint $table) {
            $table->bigIncrements('cty_id');
            $table->bigInteger('cty_cnt_id')->unsigned();
            $table->foreign('cty_cnt_id', 'tbl_cty_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('cty_stt_id')->unsigned();
            $table->foreign('cty_stt_id', 'tbl_cty_stt_id_foreign')->references('stt_id')->on('state');
            $table->string('cty_name', 125);
            $table->string('cty_iso', 50)->nullable();
            $table->char('cty_active', 1)->default('Y');
            $table->bigInteger('cty_created_by');
            $table->dateTime('cty_created_on');
            $table->bigInteger('cty_updated_by')->nullable();
            $table->dateTime('cty_updated_on')->nullable();
            $table->bigInteger('cty_deleted_by')->nullable();
            $table->dateTime('cty_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => CitySeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city');
    }
}
