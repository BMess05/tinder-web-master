<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailableTopPicksAndLastTopPickedOnColumnsToSubscriptionFeaturesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_features_status', function (Blueprint $table) {
            
            $table->dateTime('last_top_picked_on')->after('last_last_liked_on')->nullable();
            $table->integer('available_top_picked')->after('last_last_liked_on')->default(0)->nullable();

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
