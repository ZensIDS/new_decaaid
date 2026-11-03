<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3m (QA1): Halaman Kustom, yaitu halaman sederhana yang ditambah admin sendiri (mis. Syarat & Ketentuan).
 * Judul, isi, dan SEO dwibahasa (JSON id/en). Satu slug untuk kedua bahasa: /id/{slug} dan /en/{slug}.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->json('title');
            $table->json('body');                       // HTML bersih per bahasa
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_pages');
    }
};
