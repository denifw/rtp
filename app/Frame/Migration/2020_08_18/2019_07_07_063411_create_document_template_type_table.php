<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTemplateTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_template_type', function (Blueprint $table) {
            $table->bigIncrements('dtt_id');
            $table->string('dtt_description', 255);
            $table->char('dtt_active', 1)->default('Y');
            $table->bigInteger('dtt_created_by');
            $table->dateTime('dtt_created_on');
            $table->bigInteger('dtt_updated_by')->nullable();
            $table->dateTime('dtt_updated_on')->nullable();
            $table->bigInteger('dtt_deleted_by')->nullable();
            $table->dateTime('dtt_deleted_on')->nullable();
            $table->unique('dtt_description', 'tbl_dtt_description_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentTemplateTypeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_template_type');
    }
}
