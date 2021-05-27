<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action', function (Blueprint $table) {
            $table->bigIncrements('ac_id');
            $table->bigInteger('ac_srt_id')->unsigned();
            $table->foreign('ac_srt_id', 'tbl_ac_srt_id_foreign')->references('srt_id')->on('service_term');
            $table->string('ac_code', 125);
            $table->string('ac_description', 255);
            $table->string('ac_style', 125)->nullable();
            $table->integer('ac_order');
            $table->bigInteger('ac_created_by');
            $table->dateTime('ac_created_on');
            $table->bigInteger('ac_updated_by')->nullable();
            $table->dateTime('ac_updated_on')->nullable();
            $table->bigInteger('ac_deleted_by')->nullable();
            $table->dateTime('ac_deleted_on')->nullable();
            $table->unique(['ac_code', 'ac_srt_id'], 'tbl_ac_code_srt_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ActionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action');
    }
}
