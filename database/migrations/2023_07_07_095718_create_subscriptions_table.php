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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('search_filters')->nullable();
            $table->string('like_per_day')->nullable();
            $table->string('video_call')->nullable();
            $table->string('who_like_me');
            $table->string('who_view_me');
            $table->string('undo_profile')->nullable();
            $table->string('read_receipt')->nullable();
            $table->string('travel_mode')->nullable();
            $table->string('profile_badge')->nullable();
            // $table->string('message_per_match')->nullable();
            // $table->string('coin');
            $table->string('price');
            // $table->string('currency_code');
            $table->string('month');
            $table->string('plan_duration')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('google_plan_id')->nullable();
            $table->string('apple_plan_id')->nullable();
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
        Schema::dropIfExists('subscriptions');
    }
};
