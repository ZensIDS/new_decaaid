<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3h: Klien & Mitra. Kategori dikelola admin (nama dwibahasa JSON id/en); klien = logo + nama opsional.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('client_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->json('name');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_category_id')->constrained('client_categories')->restrictOnDelete();
            $table->string('name', 120)->nullable();          // opsional (K30); kosong = hanya logo
            $table->string('logo', 255);                      // path relatif ke public/
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('show_on_home')->default(false);  // strip logo di beranda
            $table->boolean('permission_ok')->default(false); // izin tampil, catatan INTERNAL (tidak tampil di situs)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_category_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_categories');
    }
};
