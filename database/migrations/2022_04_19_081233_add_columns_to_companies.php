<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('password')->default(bcrypt('12345678'));
            $table->tinyInteger('is_verified')->default(0)->comment="0:NotVerified, 1:Verified";
            $table->renameColumn('contact_email', 'email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['password', 'is_verified']);
            $table->renameColumn('email', 'contact_email');
        });
    }
}
