<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_reasons', function (Blueprint $table) {
            $table->renameColumn('reason_text', 'reason_text_en');
            $table->string('reason_text_de', 3000)->after('reason_text');
            $table->string('reason_text_tr', 3000)->after('reason_text_de');
            $table->dropColumn('language');
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
            $table->renameColumn('reason_text_en', 'reason_text');
            $table->dropColumn('reason_text_de');
            $table->dropColumn('reason_text_tr');
            $table->string('language')->comment('en= english, ge= germen, tr = turkish')->after('reason_text_en');
        });
    }
}
