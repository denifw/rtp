<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency', function (Blueprint $table) {
            $table->bigIncrements('cur_id');
            $table->bigInteger('cur_cnt_id')->unsigned();
            $table->foreign('cur_cnt_id', 'tbl_cur_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->string('cur_name', 125);
            $table->string('cur_iso', 5);
            $table->char('cur_active', 1)->default('Y');
            $table->bigInteger('cur_created_by');
            $table->dateTime('cur_created_on');
            $table->bigInteger('cur_updated_by')->nullable();
            $table->dateTime('cur_updated_on')->nullable();
            $table->bigInteger('cur_deleted_by')->nullable();
            $table->dateTime('cur_deleted_on')->nullable();
            $table->unique('cur_iso', 'tbl_cur_iso_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => CurrencySeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency');
    }
}
