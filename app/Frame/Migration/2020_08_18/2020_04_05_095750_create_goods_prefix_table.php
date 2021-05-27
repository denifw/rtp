<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsPrefixTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_prefix', function (Blueprint $table) {
            $table->bigIncrements('gpf_id');
            $table->bigInteger('gpf_gd_id')->unsigned();
            $table->foreign('gpf_gd_id', 'tbl_gpf_gd_id_foreign')->references('gd_id')->on('goods');
            $table->string('gpf_prefix', 125);
            $table->bigInteger('gpf_created_by');
            $table->dateTime('gpf_created_on');
            $table->bigInteger('gpf_updated_by')->nullable();
            $table->dateTime('gpf_updated_on')->nullable();
            $table->bigInteger('gpf_deleted_by')->nullable();
            $table->dateTime('gpf_deleted_on')->nullable();
        });
        // \Illuminate\Support\Facades\Artisan::call('db:seed', [
        //     '--class' => GoodsPrefixSeeder::class,
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_prefix');
    }
}
