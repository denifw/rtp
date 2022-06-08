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
            $table->uuid('dtt_id')->primary();
            $table->string('dtt_code', 128);
            $table->string('dtt_description', 256);
            $table->char('dtt_active', 1)->default('Y');
            $table->uuid('dtt_created_by');
            $table->dateTime('dtt_created_on');
            $table->uuid('dtt_updated_by')->nullable();
            $table->dateTime('dtt_updated_on')->nullable();
            $table->uuid('dtt_deleted_by')->nullable();
            $table->dateTime('dtt_deleted_on')->nullable();
            $table->unique('dtt_code', 'tbl_dtt_code_unique');
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
