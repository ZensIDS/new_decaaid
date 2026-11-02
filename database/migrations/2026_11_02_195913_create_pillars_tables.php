<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3c: Pilar Bisnis dan Brand (QA8).
 * Nama pilar dan nama brand berupa teks biasa (nama merek, tidak diterjemahkan).
 * Deskripsi brand dan isi "layanan" disimpan dwibahasa sebagai JSON {"id": "...", "en": "..."} (K5, K32).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 120);
            $table->string('icon', 40)->default('briefcase'); // nama ikon di components/ui/icon
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained('pillars')->restrictOnDelete();
            $table->string('slug', 80)->unique();
            $table->string('name', 120);
            $table->string('logo', 255)->nullable();      // path relatif ke public/, mis. img/logo/x.png atau storage/brands/...
            $table->json('description')->nullable();      // {"id": ..., "en": ...}
            $table->string('instagram', 120)->nullable();
            $table->string('tiktok', 120)->nullable();
            $table->string('url', 255)->nullable();       // tombol "Selengkapnya" ke situs brand
            $table->json('services')->nullable();         // {"title": {id,en}, "items": [{"title": {id,en}, "text": {id,en}}]}
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_visible')->default(true);
            $table->string('translation_status', 12)->default('manual'); // manual | auto | reviewed
            $table->boolean('is_dummy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brands');
        Schema::dropIfExists('pillars');
    }
};