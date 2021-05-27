<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnershipTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ownership_type', function (Blueprint $table) {
            $table->bigIncrements('owt_id');
            $table->string('owt_name', 255);
            $table->char('owt_active', 1)->default('Y');
            $table->bigInteger('owt_created_by');
            $table->dateTime('owt_created_on');
            $table->bigInteger('owt_updated_by')->nullable();
            $table->dateTime('owt_updated_on')->nullable();
            $table->bigInteger('owt_deleted_by')->nullable();
            $table->dateTime('owt_deleted_on')->nullable();
            $table->unique('owt_name', 'tbl_owt_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => OwnershipTypeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ownership_type');
    }
}
