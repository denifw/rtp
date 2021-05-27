<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relation', function (Blueprint $table) {
            $table->bigIncrements('rel_id');
            $table->bigInteger('rel_ss_id')->nullable();
            $table->foreign('rel_ss_id', 'tbl_rel_ss_id_foreign')->references('ss_id')->on('system_setting');
            $table->string('rel_number', 50);
            $table->string('rel_name', 255);
            $table->string('rel_short_name', 25);
            $table->string('rel_website', 125)->nullable();
            $table->string('rel_email', 255)->nullable();
            $table->string('rel_phone', 25)->nullable();
            $table->string('rel_vat', 25)->nullable();
            $table->json('rel_remark')->nullable();
            $table->char('rel_owner', 1)->default('N');
            $table->char('rel_active', 1)->default('Y');
            $table->bigInteger('rel_created_by');
            $table->dateTime('rel_created_on');
            $table->bigInteger('rel_updated_by')->nullable();
            $table->dateTime('rel_updated_on')->nullable();
            $table->bigInteger('rel_deleted_by')->nullable();
            $table->dateTime('rel_deleted_on')->nullable();
            $table->unique(['rel_ss_id', 'rel_short_name'], 'tbl_rel_ss_id_short_name_unique');
            $table->unique(['rel_ss_id', 'rel_number'], 'tbl_rel_ss_id_number_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => RelationSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relation');
    }
}
