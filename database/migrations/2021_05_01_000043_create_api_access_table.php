<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_access', function (Blueprint $table) {
            $table->uuid('aa_id')->primary();
            $table->string('aa_name', 128);
            $table->string('aa_description', 256);
            $table->char('aa_default', 1)->default('Y');
            $table->char('aa_active', 1)->default('Y');
            $table->uuid('aa_created_by');
            $table->dateTime('aa_created_on');
            $table->uuid('aa_updated_by')->nullable();
            $table->dateTime('aa_updated_on')->nullable();
            $table->uuid('aa_deleted_by')->nullable();
            $table->dateTime('aa_deleted_on')->nullable();
            $table->string('aa_deleted_reason', 256)->nullable();
            $table->unique('aa_name', 'tbl_aa_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ApiAccessSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_access');
    }
}
