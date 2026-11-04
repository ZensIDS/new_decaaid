<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat revisi Halaman Kustom (QA18, K32): salinan isi sebelum setiap perubahan, untuk fitur "kembalikan versi lama".
 * Pola sama dengan news_revisions. Ikut terhapus saat halamannya dihapus.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_page_id')->constrained('custom_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_page_revisions');
    }
};
