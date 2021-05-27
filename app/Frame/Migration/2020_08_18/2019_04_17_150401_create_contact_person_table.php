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
            $table->bigIncrements('cp_id');
            $table->string('cp_number', 125);
            $table->string('cp_name', 125);
            $table->string('cp_email', 125)->nullable();
            $table->string('cp_phone', 25)->nullable();
            $table->bigInteger('cp_of_id')->unsigned();
            $table->foreign('cp_of_id', 'tbl_cp_of_id_foreign')->references('of_id')->on('office');
            $table->char('cp_office_manager', 1)->default('Y');
            $table->char('cp_active', 1)->default('Y');
            $table->bigInteger('cp_created_by');
            $table->dateTime('cp_created_on');
            $table->bigInteger('cp_updated_by')->nullable();
            $table->dateTime('cp_updated_on')->nullable();
            $table->bigInteger('cp_deleted_by')->nullable();
            $table->dateTime('cp_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => ContactPersonSeeder::class,
        ]);

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
