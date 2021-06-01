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
            $table->uuid('lg_id')->primary();
            $table->string('lg_locale', 128);
            $table->string('lg_iso', 128);
            $table->char('lg_active', 1)->default('Y');
            $table->uuid('lg_created_by');
            $table->dateTime('lg_created_on');
            $table->uuid('lg_updated_by')->nullable();
            $table->dateTime('lg_updated_on')->nullable();
            $table->uuid('lg_deleted_by')->nullable();
            $table->dateTime('lg_deleted_on')->nullable();
            $table->unique('lg_iso', 'lg_iso_unique');
            $table->string('lg_deleted_reason', 256)->nullable();
        });
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
