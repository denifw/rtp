<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomsDocumentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customs_document_type', function (Blueprint $table) {
            $table->bigIncrements('cdt_id');
            $table->string('cdt_name', 125);
            $table->char('cdt_active', 1)->default('Y');
            $table->bigInteger('cdt_created_by');
            $table->dateTime('cdt_created_on');
            $table->bigInteger('cdt_updated_by')->nullable();
            $table->dateTime('cdt_updated_on')->nullable();
            $table->bigInteger('cdt_deleted_by')->nullable();
            $table->dateTime('cdt_deleted_on')->nullable();
            $table->unique('cdt_name', 'tbl_cdt_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => CustomsDocumentTypeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customs_type');
    }
}
