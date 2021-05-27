<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_log', function (Blueprint $table) {
            $table->bigIncrements('ul_id');
            $table->string('ul_media', 125);
            $table->string('ul_route', 255);
            $table->string('ul_action', 255);
            $table->bigInteger('ul_ref_id');
            $table->string('ul_token', 255);
            $table->jsonb('ul_param');
            $table->bigInteger('ul_created_by');
            $table->dateTime('ul_created_on');
            $table->bigInteger('ul_updated_by')->nullable();
            $table->dateTime('ul_updated_on')->nullable();
            $table->bigInteger('ul_deleted_by')->nullable();
            $table->dateTime('ul_deleted_on')->nullable();
            $table->unique(['ul_route', 'ul_action', 'ul_token'], 'ul_route_action_token_unique');
        });
    }

/**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_log');
    }
}
