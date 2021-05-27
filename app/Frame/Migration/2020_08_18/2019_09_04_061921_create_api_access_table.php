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
            $table->bigIncrements('aa_id');
            $table->string('aa_name', 125);
            $table->string('aa_description', 255);
            $table->char('aa_default', 1)->default('Y');
            $table->char('aa_active', 1)->default('Y');
            $table->bigInteger('aa_created_by');
            $table->dateTime('aa_created_on');
            $table->bigInteger('aa_updated_by')->nullable();
            $table->dateTime('aa_updated_on')->nullable();
            $table->bigInteger('aa_deleted_by')->nullable();
            $table->dateTime('aa_deleted_on')->nullable();
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
