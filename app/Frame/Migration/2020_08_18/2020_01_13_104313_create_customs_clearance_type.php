<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomsClearanceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customs_clearance_type', function (Blueprint $table) {
            $table->bigIncrements('cct_id');
            $table->string('cct_name', 125);
            $table->char('cct_active', 1)->default('Y');
            $table->bigInteger('cct_created_by');
            $table->dateTime('cct_created_on');
            $table->bigInteger('cct_updated_by')->nullable();
            $table->dateTime('cct_updated_on')->nullable();
            $table->bigInteger('cct_deleted_by')->nullable();
            $table->dateTime('cct_deleted_on')->nullable();
            $table->unique('cct_name', 'tbl_cct_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => CustomsClearanceTypeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_clearance_type');
    }
}
