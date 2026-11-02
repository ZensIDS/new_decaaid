<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B5: mencatat arah terjemahan otomatis terakhir ('id' = EN dihasilkan dari ID, 'en' = sebaliknya).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('translated_from', 2)->nullable()->after('translation_status');
        });
    }

    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('translated_from');
        });
    }
};