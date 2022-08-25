<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibleTopPicksToSubscriptionFeaturesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_features_status', function (Blueprint $table) {
            $table->integer('visible_top_picks')->default(0)->comment('max visible users in top picks')->nullable()->after('available_top_picked');
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
