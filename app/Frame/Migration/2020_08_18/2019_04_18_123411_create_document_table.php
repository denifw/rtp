<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document', function (Blueprint $table) {
            $table->bigIncrements('doc_id');
            $table->bigInteger('doc_dct_id')->unsigned();
            $table->foreign('doc_dct_id', 'tbl_doc_dct_id_foreign')->references('dct_id')->on('document_type');
            $table->bigInteger('doc_ss_id')->unsigned();
            $table->foreign('doc_ss_id', 'tbl_doc_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->bigInteger('doc_group_reference');
            $table->bigInteger('doc_type_reference')->nullable();
            $table->string('doc_file_name', 255);
            $table->double('doc_file_size');
            $table->string('doc_file_type', 125);
            $table->char('doc_public', 1)->default('Y');
            $table->bigInteger('doc_created_by');
            $table->dateTime('doc_created_on');
            $table->bigInteger('doc_updated_by')->nullable();
            $table->dateTime('doc_updated_on')->nullable();
            $table->bigInteger('doc_deleted_by')->nullable();
            $table->dateTime('doc_deleted_on')->nullable();
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => DocumentSeeder::class,
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document');
    }
}
