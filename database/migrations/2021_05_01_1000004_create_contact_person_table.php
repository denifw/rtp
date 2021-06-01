<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_person', function (Blueprint $table) {
            $table->uuid('cp_id')->primary();
            $table->string('cp_number', 64);
            $table->string('cp_name', 256);
            $table->string('cp_email', 256)->nullable();
            $table->string('cp_phone', 16)->nullable();
            $table->uuid('cp_of_id')->unsigned();
            $table->foreign('cp_of_id', 'tbl_cp_of_id_fkey')->references('of_id')->on('office');
            $table->char('cp_active', 1)->default('Y');
            $table->uuid('cp_created_by');
            $table->dateTime('cp_created_on');
            $table->uuid('cp_updated_by')->nullable();
            $table->dateTime('cp_updated_on')->nullable();
            $table->uuid('cp_deleted_by')->nullable();
            $table->dateTime('cp_deleted_on')->nullable();
            $table->string('cp_deleted_reason', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_person');
    }
}
