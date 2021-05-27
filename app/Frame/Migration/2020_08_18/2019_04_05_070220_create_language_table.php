<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('lg_id');
            $table->string('lg_locale', 125);
            $table->string('lg_iso', 125);
            $table->char('lg_active', 1)->default('Y');
            $table->bigInteger('lg_created_by');
            $table->dateTime('lg_created_on');
            $table->bigInteger('lg_updated_by')->nullable();
            $table->dateTime('lg_updated_on')->nullable();
            $table->bigInteger('lg_deleted_by')->nullable();
            $table->dateTime('lg_deleted_on')->nullable();
            $table->unique('lg_iso', 'lg_iso_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => LanguageSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
