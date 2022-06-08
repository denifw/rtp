<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_type', function (Blueprint $table) {
            $table->uuid('dct_id')->primary();
            $table->uuid('dct_dcg_id')->unsigned();
            $table->foreign('dct_dcg_id', 'tbl_dct_dcg_id_fkey')->references('dcg_id')->on('document_group');
            $table->string('dct_code', 128);
            $table->string('dct_description', 256);
            $table->string('dct_table', 128)->nullable();
            $table->string('dct_value_field', 128)->nullable();
            $table->string('dct_text_field', 128)->nullable();
            $table->char('dct_active', 1)->default('Y');
            $table->uuid('dct_created_by');
            $table->dateTime('dct_created_on');
            $table->uuid('dct_updated_by')->nullable();
            $table->dateTime('dct_updated_on')->nullable();
            $table->uuid('dct_deleted_by')->nullable();
            $table->dateTime('dct_deleted_on')->nullable();
            $table->string('dct_deleted_reason', 256)->nullable();
            $table->unique(['dct_dcg_id', 'dct_code'], 'tbl_dct_code_dcg_id_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentTypeSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_type');
    }
}
