<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3g: Proyek (Portofolio). Kolom dwibahasa disimpan sebagai JSON {"id": ..., "en": ...} (K5, K32).
 * Satu proyek terhubung ke satu pilar (+ brand opsional), sesuai QA10.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->foreignId('pillar_id')->nullable()->constrained('pillars')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->json('title');
            $table->string('client', 160)->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->json('scope')->nullable();          // {"id": ["..."], "en": ["..."]}: daftar lingkup kerja
            $table->string('image', 255)->nullable();   // path relatif ke public/
            $table->json('image_alt')->nullable();
            $table->json('gallery')->nullable();        // disiapkan (QA10); tampilan galeri menyusul
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->string('translation_status', 12)->default('manual'); // manual | auto | reviewed
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->boolean('is_dummy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};