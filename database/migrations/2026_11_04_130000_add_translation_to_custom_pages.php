<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B5: status terjemahan Halaman Kustom, sama seperti Berita. 'translation_status' = manual | auto | reviewed;
 * 'translated_from' = bahasa sumber terjemahan mesin terakhir ('id' | 'en'), dipakai untuk catatan "belum ditinjau" di situs.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->string('translation_status', 12)->default('manual')->after('sort_order');
            $table->string('translated_from', 2)->nullable()->after('translation_status');
        });
    }

    public function down()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropColumn(['translation_status', 'translated_from']);
        });
    }
};
