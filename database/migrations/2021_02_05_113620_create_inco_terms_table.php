<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncoTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inco_terms', function (Blueprint $table) {
            $table->bigIncrements('ict_id');
            $table->string('ict_name', 256);
            $table->string('ict_code', 128);
            $table->char('ict_pol', 1);
            $table->char('ict_pod', 1);
            $table->char('ict_load', 1);
            $table->char('ict_unload', 1);
            $table->bigInteger('ict_created_by');
            $table->dateTime('ict_created_on');
            $table->bigInteger('ict_updated_by')->nullable();
            $table->dateTime('ict_updated_on')->nullable();
            $table->bigInteger('ict_deleted_by')->nullable();
            $table->dateTime('ict_deleted_on')->nullable();
            $table->string('ict_deleted_reason', 256)->nullable();
            $table->uuid('ict_uid');
            $table->unique('ict_uid', 'tbl_ict_uid_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => IncoTermsSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inco_terms');
    }
}
