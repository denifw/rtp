<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serial_history', function (Blueprint $table) {
            $table->bigIncrements('sh_id');
            $table->bigInteger('sh_sn_id')->unsigned();
            $table->foreign('sh_sn_id', 'tbl_sh_sn_id_foreign')->references('sn_id')->on('serial_number');
            $table->string('sh_year', 4)->nullable();
            $table->string('sh_month', 2)->nullable();
            $table->bigInteger('sh_number');
            $table->dateTime('sh_created_on');
            $table->bigInteger('sh_created_by');
            $table->dateTime('sh_updated_on')->nullable();
            $table->bigInteger('sh_updated_by')->nullable();
            $table->dateTime('sh_deleted_on')->nullable();
            $table->bigInteger('sh_deleted_by')->nullable();
            $table->unique(['sh_sn_id', 'sh_year', 'sh_month', 'sh_number'], 'tbl_sh_sn_id_year_month_number_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => SerialHistorySeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serial_history');
    }
}
