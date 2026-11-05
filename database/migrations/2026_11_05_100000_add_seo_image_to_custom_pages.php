<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3n: gambar share (Open Graph/Twitter) per Halaman Kustom, dipilih dari Media Library. Kosong = gambar bawaan situs.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->string('seo_image', 255)->nullable()->after('seo_description');
        });
    }

    public function down()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropColumn('seo_image');
        });
    }
};
