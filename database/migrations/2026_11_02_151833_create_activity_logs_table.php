<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BA3: log aktivitas (siapa, aksi, sasaran, waktu, IP).
 * Nama pengguna disalin ke kolom user_name agar log tetap terbaca walau akunnya dihapus.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->string('action', 60)->index();
            $table->string('description', 255);
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};