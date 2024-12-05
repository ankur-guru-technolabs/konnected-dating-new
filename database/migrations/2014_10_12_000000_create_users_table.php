<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone_no')->nullable()->unique();
            $table->string('location');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('job');
            $table->text('bio');
            $table->string('company');
            $table->string('gender');
            $table->string('age');
            $table->string('height');
            $table->string('education');
            $table->string('industry');
            $table->string('salary');
            $table->string('body_type')->nullable();
            $table->string('children');
            $table->string('lastseen')->nullable();
            $table->string('user_type');
            $table->string('email_verified')->default(0);
            $table->string('phone_verified')->default(0);
            $table->string('otp_verified')->default(0);
            $table->string('faith');
            $table->string('ethnticity');
            $table->string('hobbies');
            $table->string('undo_remaining_count')->nullable(0);
            $table->string('last_undo_date')->nullable();
            $table->string('status')->default(1);
            $table->string('is_notification_mute')->default(0);
            $table->string('fcm_token')->nullable();
            $table->string('device_token')->nullable();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
