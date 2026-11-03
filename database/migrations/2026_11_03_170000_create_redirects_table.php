<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3n (bagian Redirect): pengalihan URL lama ke baru (mis. URL WordPress). Hanya dipakai saat sebuah
 * alamat tidak ditemukan (404), jadi tidak pernah menimpa halaman yang masih ada.
 * from_path disimpan ternormalisasi (huruf kecil, diawali "/", tanpa "/" di akhir, tanpa query).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 300)->unique();
            $table->string('to_path', 500);
            $table->unsignedSmallInteger('status_code')->default(301); // 301 | 302
            $table->boolean('is_active')->default(true)->index();
            $table->string('note', 200)->nullable();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('redirects');
    }
};
