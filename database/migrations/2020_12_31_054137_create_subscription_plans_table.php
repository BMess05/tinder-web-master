<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('plan_name')->comment('set plan name');
            
            $table->boolean('is_active')->default(0);

            $table->boolean('super_likes')->default(0)->comment('set Super Likes feature on/off');
            $table->integer('super_likes_count')->default(0)->comment('set number of super_likes')->nullable();
            $table->integer('super_likes_duration')->default(0)->comment('set cycle for super_likes in days')->nullable();

            $table->boolean('unlimited_likes')->default(0)->comment('set Unlimited Likes feature on/off');

            $table->boolean('boost')->default(0)->comment('set Boost feature on/off');
            $table->integer('boost_count')->default(0)->comment('set boost_count')->nullable();
            $table->integer('boost_duration')->nullable()->comment('set cycle for boost_duration in days')->nullable();

            $table->boolean('passport')->default(0)->comment('set swipe around the world to anywhere feature on/off');

            $table->boolean('unlimited_rewinds')->default(0)->comment('Set Rematch with any of your expired matches feature on/off');

            $table->boolean('ads')->default(0)->comment('Set ads on/off');

            $table->boolean('top_picks')->default(0)->comment('Set Top Picks feature on/off');

            $table->boolean('see_who_likes_me')->default(0)->comment('Set who Likes You before you Like or Nope feature on/off');

            $table->boolean('last_likes')->default(0)->comment('Set see the Likes you’ve sent in the last 7 days feature on/off');
            $table->integer('last_likes_duration')->default(0)->comment('Set last_likes duration')->nullable();

            $table->boolean('priority_likes')->default(0)->comment('Set Like prioritized over others with Priority Likes feature on/off');
            
            $table->boolean('attach_message')->default(0)->comment('Set message before matching by attaching a message to your Super Like feature on/off');

            $table->string('apple_id')->nullable();
            $table->string('android_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('availability')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('price')->nullable();
            $table->string('offer_price')->nullable();
            $table->integer('grace_period')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->softDeletes();
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
        Schema::dropIfExists('subscription_plans');
    }
}
