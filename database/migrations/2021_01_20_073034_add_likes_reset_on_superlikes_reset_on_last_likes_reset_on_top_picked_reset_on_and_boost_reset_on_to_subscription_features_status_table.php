<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLikesResetOnSuperlikesResetOnLastLikesResetOnTopPickedResetOnAndBoostResetOnToSubscriptionFeaturesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_features_status', function (Blueprint $table) {
            $table->dateTime('likes_reset_on')->nullable()->after('last_liked_on');
            $table->dateTime('super_likes_reset_on')->nullable()->after('last_super_liked_on');
            $table->dateTime('last_likes_reset_on')->nullable()->after('last_last_liked_on');
            $table->dateTime('top_picked_reset_on')->nullable()->after('last_top_picked_on');
            $table->dateTime('boost_reset_on')->nullable()->after('last_boosted_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_features_status', function (Blueprint $table) {
            //
        });
    }
}
