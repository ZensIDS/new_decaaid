<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3j: isi halaman tunggal (satu baris per halaman, isi JSON dengan repeater dwibahasa).
 * Dipakai pertama kali oleh Keberlanjutan (kunci "sustainability"); B3e (Beranda), B3f (Tentang Kami),
 * dan B3k (Karir) memakai tabel yang sama. Tanpa baris = situs memakai teks contoh di kode.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page', 60)->unique();
            $table->json('content');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('page_contents');
    }
};
