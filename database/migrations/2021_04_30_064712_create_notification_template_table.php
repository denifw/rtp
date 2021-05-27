<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('user_group_notification');
        Schema::dropIfExists('notification_receiver');
        Schema::dropIfExists('notification');
        Schema::dropIfExists('page_notification');

        Schema::create('notification_template', function (Blueprint $table) {
            $table->bigIncrements('nt_id');
             $table->string('nt_code', 256);
            $table->string('nt_description', 256);
            $table->jsonb('nt_message_fields');
            $table->string('nt_mail_path', 256);
            $table->char('nt_active', 1)->default('Y');
            $table->bigInteger('nt_created_by');
            $table->dateTime('nt_created_on');
            $table->bigInteger('nt_updated_by')->nullable();
            $table->dateTime('nt_updated_on')->nullable();
            $table->bigInteger('nt_deleted_by')->nullable();
            $table->dateTime('nt_deleted_on')->nullable();
            $table->string('nt_deleted_reason', 256)->nullable();
            $table->uuid('nt_uid');
            $table->unique('nt_uid', 'tbl_nt_uid_unique');
            $table->unique(['nt_code'], 'tbl_nt_code_unique');
        });
//        \Illuminate\Support\Facades\Artisan::call('db:seed', [
//            '--class' => NotificationTemplateSeeder::class,
//        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_template');
    }
}
