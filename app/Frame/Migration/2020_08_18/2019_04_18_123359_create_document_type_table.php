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
            $table->bigIncrements('dct_id');
            $table->bigInteger('dct_dcg_id')->unsigned();
            $table->foreign('dct_dcg_id', 'tbl_dct_dcg_id_foreign')->references('dcg_id')->on('document_group');
            $table->string('dct_code', 125);
            $table->string('dct_description', 255);
            $table->string('dct_table', 125)->nullable();
            $table->string('dct_value_field', 125)->nullable();
            $table->string('dct_text_field', 125)->nullable();
            $table->char('dct_master', 1)->default('Y');
            $table->char('dct_active', 1)->default('Y');
            $table->bigInteger('dct_created_by');
            $table->dateTime('dct_created_on');
            $table->bigInteger('dct_updated_by')->nullable();
            $table->dateTime('dct_updated_on')->nullable();
            $table->bigInteger('dct_deleted_by')->nullable();
            $table->dateTime('dct_deleted_on')->nullable();
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
