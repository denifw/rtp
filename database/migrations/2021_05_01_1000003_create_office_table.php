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
            $table->uuid('of_id')->primary();
            $table->uuid('of_rel_id')->unsigned();
            $table->foreign('of_rel_id', 'tbl_of_rel_id_fkey')->references('rel_id')->on('relation');
            $table->string('of_name', 128);
            $table->char('of_invoice', 1)->default('N');
            $table->string('of_address', 256)->nullable();
            $table->uuid('of_cnt_id')->unsigned()->nullable();
            $table->foreign('of_cnt_id', 'tbl_of_cnt_id_fkey')->references('cnt_id')->on('country');
            $table->uuid('of_stt_id')->unsigned()->nullable();
            $table->foreign('of_stt_id', 'tbl_of_stt_id_fkey')->references('stt_id')->on('state');
            $table->uuid('of_cty_id')->unsigned()->nullable();
            $table->foreign('of_cty_id', 'tbl_of_cty_id_fkey')->references('cty_id')->on('city');
            $table->uuid('of_dtc_id')->unsigned()->nullable();
            $table->foreign('of_dtc_id', 'tbl_of_dtc_id_fkey')->references('dtc_id')->on('district');
            $table->string('of_postal_code', 16)->nullable();
            $table->float('of_longitude')->nullable();
            $table->uuid('of_cp_id')->nullable();
            $table->float('of_latitude')->nullable();
            $table->char('of_active', 1)->default('Y');
            $table->uuid('of_created_by');
            $table->dateTime('of_created_on');
            $table->uuid('of_updated_by')->nullable();
            $table->dateTime('of_updated_on')->nullable();
            $table->uuid('of_deleted_by')->nullable();
            $table->dateTime('of_deleted_on')->nullable();
            $table->string('of_deleted_reason', 256)->nullable();
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
