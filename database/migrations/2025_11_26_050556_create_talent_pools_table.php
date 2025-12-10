<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('talent_pools', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('nim')->index();
            $table->string('nama');
            $table->string('fakultas');
            $table->string('jurusan');
            $table->string('angkatan');

            // Data Lomba (Lengkap)
            $table->string('nama_lomba');
            $table->string('hasil_lomba')->nullable();   // Juara 1, Finalis, dll
            $table->string('tahun_lomba')->nullable();   // 2022, 2023
            $table->string('tingkat_lomba')->nullable(); // Nasional, Internasional
            $table->string('tipe_lomba');                // Olahraga, Seni

            // Data UKM
            $table->string('kategori_ukm');              // Kategori Lomba/UKM
            $table->text('ukm_yang_diikuti')->nullable();
            $table->string('match_or_not');              // Match / No Match

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('talent_pools');
    }
};
