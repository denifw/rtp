<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('district', function (Blueprint $table) {
            $table->bigIncrements('dtc_id');
            $table->bigInteger('dtc_cnt_id')->unsigned();
            $table->foreign('dtc_cnt_id', 'tbl_dtc_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('dtc_stt_id')->unsigned();
            $table->foreign('dtc_stt_id', 'tbl_dtc_stt_id_foreign')->references('stt_id')->on('state');
            $table->bigInteger('dtc_cty_id')->unsigned();
            $table->foreign('dtc_cty_id', 'tbl_dtc_cty_id_foreign')->references('cty_id')->on('city');
            $table->string('dtc_name', 125);
            $table->string('dtc_iso', 50)->nullable();
            $table->char('dtc_active', 1)->default('Y');
            $table->bigInteger('dtc_created_by');
            $table->dateTime('dtc_created_on');
            $table->bigInteger('dtc_updated_by')->nullable();
            $table->dateTime('dtc_updated_on')->nullable();
            $table->bigInteger('dtc_deleted_by')->nullable();
            $table->dateTime('dtc_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DistrictSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('district');
    }
}
