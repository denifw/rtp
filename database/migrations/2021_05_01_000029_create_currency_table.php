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
            $table->uuid('cur_id')->primary();
            $table->uuid('cur_cnt_id')->unsigned();
            $table->foreign('cur_cnt_id', 'tbl_cur_cnt_id_fkey')->references('cnt_id')->on('country');
            $table->string('cur_name', 128);
            $table->string('cur_iso', 16);
            $table->char('cur_active', 1)->default('Y');
            $table->uuid('cur_created_by');
            $table->dateTime('cur_created_on');
            $table->uuid('cur_updated_by')->nullable();
            $table->dateTime('cur_updated_on')->nullable();
            $table->uuid('cur_deleted_by')->nullable();
            $table->dateTime('cur_deleted_on')->nullable();
            $table->string('cur_deleted_reason', 256)->nullable();
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
