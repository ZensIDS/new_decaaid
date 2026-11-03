<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3l / B4: pesan dari formulir Kontak (kotak masuk admin). Disimpan 12 bulan (K31), lalu dihapus
 * oleh perintah `messages:prune`.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('subject', 40)->default('general'); // kunci subjek (lihat Content::contactPage())
            $table->text('message');
            $table->string('locale', 5)->default('id');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('notified')->default(false); // email notifikasi ke info@decaa.id terkirim
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_messages');
    }
};
