<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3i: Penghargaan & Sertifikat. Sengaja sederhana (K30 / QA11): tanpa jenis, penerbit, dan PDF.
 * Judul, deskripsi, dan alt gambar dwibahasa (JSON {"id": ..., "en": ...}).
 * "Terbaru" ditentukan manual lewat penanda unggulan (satu saja, tampil besar di halaman Penghargaan).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedSmallInteger('year')->index();
            $table->string('image', 255)->nullable();   // path relatif ke public/
            $table->json('image_alt')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->string('translation_status', 12)->default('manual'); // manual | auto | reviewed
            $table->boolean('is_dummy')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('awards');
    }
};
