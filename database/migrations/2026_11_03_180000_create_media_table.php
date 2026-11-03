<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3b: Media Library. Katalog berkas gambar: unggahan admin (storage/app/public) dan aset bawaan (public/img).
 * Modul lain tetap menyimpan path berkas di kolomnya sendiri; tabel ini mencatat nama, alt, ukuran, dan thumbnail,
 * dan pelacak pemakaian (MediaUsage) mencari path itu di semua modul.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255)->unique();            // relatif ke public/, mis. storage/news/2026/11/abc.webp
            $table->string('name', 160);                      // nama tampilan (bukan nama berkas)
            $table->string('mime', 60)->nullable();
            $table->unsignedInteger('size')->default(0);      // byte
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('source', 10)->default('upload');  // upload | bundled
            $table->string('folder', 60)->nullable()->index();
            $table->json('alt')->nullable();                  // {id, en}
            $table->string('thumb', 255)->nullable();         // path thumbnail (relatif ke public/)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('source');
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
};
