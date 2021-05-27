<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state', function (Blueprint $table) {
            $table->bigIncrements('stt_id');
            $table->bigInteger('stt_cnt_id')->unsigned();
            $table->foreign('stt_cnt_id', 'tbl_stt_cnt_id_foreign')->references('cnt_id')->on('country');
            $table->string('stt_name', 125);
            $table->string('stt_iso', 50)->nullable();
            $table->char('stt_active', 1)->default('Y');
            $table->bigInteger('stt_created_by');
            $table->dateTime('stt_created_on');
            $table->bigInteger('stt_updated_by')->nullable();
            $table->dateTime('stt_updated_on')->nullable();
            $table->bigInteger('stt_deleted_by')->nullable();
            $table->dateTime('stt_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => StateSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state');
    }
}
