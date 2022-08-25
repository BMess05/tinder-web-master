<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColoumnLanguageToReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_reasons', function (Blueprint $table) {
            $table->string('language')->comment('en= english, ge= germen, tr = turkish')->after('reason_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_reasons', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}
