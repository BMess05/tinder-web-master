<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLikesCountAndLikesDurationToSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('likes_count')->default(0)->comment('set number of  likes')->nullable()->after('unlimited_likes');
            $table->integer('likes_duration')->default(0)->comment('set cycle for likes in days')->nullable()->after('unlimited_likes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'likes_duration']);
        });
    }
}
