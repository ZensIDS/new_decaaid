<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3n bagian 2c: judul dan deskripsi SEO (dwibahasa JSON id/en) untuk halaman pilar dan halaman brand.
 * Kosong = judul/deskripsi bawaan halaman.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('pillars', function (Blueprint $table) {
            $table->json('seo_title')->nullable()->after('icon');
            $table->json('seo_description')->nullable()->after('seo_title');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->json('seo_title')->nullable()->after('services');
            $table->json('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down()
    {
        Schema::table('pillars', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description']);
        });
    }
};
