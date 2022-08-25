<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTableSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        $data = [
            [
                'key' => 'ANDROID_ACCESS_TOKEN',
                'value' => 'ya29.a0ARrdaM9LMc__N5WFMNRJgTAGXletpH8O0g9LQlAKyxHpFARPLTTCcIWhtfl48pWjwC8Scs6R2RVXq99eZ_FCuQ6ZD_zaSO4mQVbUZEUGJ22UEf7hp5Z_ovRSVok9pLkYaaVQNr_zw6cwjmDst02hzW2t5vd3'
            ],
            [
                'key' => 'ANDROID_REFRESH_TOKEN',
                'value' => '1//0gPG_FvMS-6ZVCgYIARAAGBASNwF-L9IrbxGIf7zq2BrYq1jEYCvPTnse2wqqTRIfZTWGXfCRKAFp5U9ruhngAj6-0fXNtduQ3OU'
            ],
            [
                'key' => 'EXPIIRES_IN',
                'value' => '3599'
            ]
        ];

        foreach ($data as $row) {
            DB::table('settings')->insert([
                'key' => $row['key'],
                'value' => $row['value']
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
