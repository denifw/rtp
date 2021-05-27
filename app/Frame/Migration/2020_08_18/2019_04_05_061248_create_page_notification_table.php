<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_notification', function (Blueprint $table) {
            $table->bigIncrements('pn_id');
            $table->bigInteger('pn_pg_id')->unsigned();
            $table->foreign('pn_pg_id', 'tbl_pn_pg_id_foreign')->references('pg_id')->on('page');
            $table->string('pn_name', 125);
            $table->string('pn_description', 255);
            $table->json('pn_url_fields')->nullable();
            $table->json('pn_message_fields')->nullable();
            $table->char('pn_active', 1)->default('Y');
            $table->dateTime('pn_created_on');
            $table->bigInteger('pn_created_by');
            $table->dateTime('pn_updated_on')->nullable();
            $table->bigInteger('pn_updated_by')->nullable();
            $table->dateTime('pn_deleted_on')->nullable();
            $table->bigInteger('pn_deleted_by')->nullable();
            $table->unique(['pn_pg_id', 'pn_name'], 'tbl_pn_pg_id_name_unique');
        });
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => PageNotificationSeeder::class,
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_notification');
    }
}
