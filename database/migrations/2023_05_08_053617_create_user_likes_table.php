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
        Schema::create('user_likes', function (Blueprint $table) {
            $table->id();
            $table->string('like_from')->index();
            $table->string('like_to')->index();
            $table->string('match_id')->nullable()->index();
            $table->tinyInteger('match_status')->default(2)->comment('0: unmatched, 1: matched, 2: nothing')->index();
            $table->tinyInteger('status')->nullable()->comment('0: dislike, 1: like')->index();
            $table->tinyInteger('can_chat')->default(1)->comment('0: No, 1: Yes')->index();
            $table->timestamp('matched_at')->nullable();
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
        Schema::dropIfExists('user_likes');
    }
};
