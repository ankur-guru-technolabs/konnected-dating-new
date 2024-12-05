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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->string('start_date')->nullable();
            $table->string('expire_date')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('search_filters')->nullable();
            $table->integer('like_per_day')->nullable();
            $table->string('video_call')->nullable();
            $table->string('who_like_me');
            $table->string('who_view_me');
            $table->string('undo_profile')->nullable();
            $table->string('read_receipt')->nullable();
            $table->string('travel_mode')->nullable();
            $table->string('profile_badge')->nullable();
            $table->string('price');
            $table->string('currency_code');
            $table->integer('month');
            $table->integer('plan_duration')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('google_plan_id')->nullable();
            $table->string('apple_plan_id')->nullable();
            $table->text('order_id')->nullable();
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
        Schema::dropIfExists('user_subscriptions');
    }
};
