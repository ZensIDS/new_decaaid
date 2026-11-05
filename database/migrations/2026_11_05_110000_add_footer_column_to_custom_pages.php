<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B3o: kolom footer tujuan tautan Halaman Kustom. 'company' = kolom Perusahaan (bawaan, perilaku lama),
 * 'units' = kolom Pilar Bisnis. Halaman yang sudah ada tetap di kolom Perusahaan.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->string('footer_column', 10)->default('company')->after('show_in_footer');
        });
    }

    public function down()
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropColumn('footer_column');
        });
    }
};
