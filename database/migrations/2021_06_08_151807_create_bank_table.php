<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank', function (Blueprint $table) {
            $table->uuid('bn_id')->primary();
            $table->string('bn_short_name', 125);
            $table->string('bn_name', 255);
            $table->char('bn_active', 1)->default('Y');
            $table->uuid('bn_created_by');
            $table->dateTime('bn_created_on');
            $table->uuid('bn_updated_by')->nullable();
            $table->dateTime('bn_updated_on')->nullable();
            $table->uuid('bn_deleted_by')->nullable();
            $table->dateTime('bn_deleted_on')->nullable();
            $table->string('bn_deleted_reason', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank');
    }
}
