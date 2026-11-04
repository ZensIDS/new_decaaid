<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B5: bahasa sumber terjemahan mesin terakhir ('id' | 'en') untuk Penghargaan dan Proyek,
 * dipakai catatan "belum ditinjau" di situs. 'translation_status' sudah ada sejak tabel dibuat.
 */
return new class extends Migration
{
    public function up()
    {
        foreach (['awards', 'projects'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('translated_from', 2)->nullable()->after('translation_status');
            });
        }
    }

    public function down()
    {
        foreach (['awards', 'projects'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('translated_from');
            });
        }
    }
};
