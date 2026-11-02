<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3d: Berita, Kategori Berita, dan Riwayat Revisi.
 * Kolom dwibahasa disimpan sebagai JSON {"id": "...", "en": "..."} (K5, K32).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->json('name');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->foreignId('news_category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();         // HTML bersih per bahasa
            $table->string('image', 255)->nullable(); // path relatif ke public/, mis. storage/news/...
            $table->json('image_alt')->nullable();
            $table->string('status', 12)->default('draft')->index(); // draft | published | scheduled
            $table->timestamp('published_at')->nullable()->index();
            $table->string('translation_status', 12)->default('manual'); // manual | auto | reviewed
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->boolean('is_dummy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('news_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_revisions');
        Schema::dropIfExists('news');
        Schema::dropIfExists('news_categories');
    }
};
