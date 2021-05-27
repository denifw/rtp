<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office', function (Blueprint $table) {
            $table->bigIncrements('of_id');
            $table->bigInteger('of_rel_id')->unsigned()->nullable();
            $table->foreign('of_rel_id', 'tbl_of_rel_id_foreign')->references('rel_id')->on('relation');
            $table->string('of_name', 125);
            $table->char('of_main', 1)->default('N');
            $table->char('of_invoice', 1)->default('N');
            $table->string('of_address', 255)->nullable();
            $table->bigInteger('of_cnt_id')->unsigned()->nullable();
            $table->foreign('of_cnt_id', 'tbl_of_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->bigInteger('of_stt_id')->unsigned()->nullable();
            $table->foreign('of_stt_id', 'tbl_of_stt_id_foreign')->references('stt_id')->on('state');
            $table->bigInteger('of_cty_id')->unsigned()->nullable();
            $table->foreign('of_cty_id', 'tbl_of_cty_id_foreign')->references('cty_id')->on('city');
            $table->bigInteger('of_dtc_id')->unsigned()->nullable();
            $table->foreign('of_dtc_id', 'tbl_of_dtc_id_foreign')->references('dtc_id')->on('district');
            $table->string('of_postal_code', 10)->nullable();
            $table->float('of_longitude')->nullable();
            $table->float('of_latitude')->nullable();
            $table->char('of_active', 1)->default('Y');
            $table->bigInteger('of_created_by');
            $table->dateTime('of_created_on');
            $table->bigInteger('of_updated_by')->nullable();
            $table->dateTime('of_updated_on')->nullable();
            $table->bigInteger('of_deleted_by')->nullable();
            $table->dateTime('of_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => OfficeSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('office');
    }
}
