<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Impor WordPress: menyimpan ID post asal agar impor bisa diulang tanpa menggandakan berita.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->unsignedBigInteger('wp_id')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['wp_id']);
            $table->dropColumn('wp_id');
        });
    }
};