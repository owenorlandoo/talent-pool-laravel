<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TalentPool;

class TalentSeeder extends Seeder
{
    public function run()
    {
        // 1. Target File PASTI
        $path = base_path('FIX_DATA.csv');

        if (!file_exists($path)) {
            $this->command->error("❌ FILE 'FIX_DATA.csv' TIDAK DITEMUKAN!");
            $this->command->line("Pastikan Anda sudah me-rename file CSV Tracking menjadi 'FIX_DATA.csv' dan menaruhnya di folder root.");
            return;
        }

        // 2. Bersihkan Database
        DB::table('talent_pools')->truncate();
        $this->command->info("🧹 Database bersih. Membaca FIX_DATA.csv...");

        // 3. Baca File
        $handle = fopen($path, "r");

        // Deteksi Delimiter
        $line = fgets($handle);
        $delimiter = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
        rewind($handle);

        $this->command->info("ℹ️  Delimiter terdeteksi: '$delimiter'");

        $firstRow = true;
        $count = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            // Skip Header
            if ($firstRow) {
                $firstRow = false;
                // DEBUG: Cek Header
                $this->command->info("📊 Jumlah Kolom Terdeteksi: " . count($data));
                if(count($data) < 13) {
                    $this->command->error("⚠️ PERINGATAN: File ini sepertinya kurang dari 13 kolom! Cek apakah ini file Tracking yang benar?");
                }
                continue;
            }

            // Skip baris kosong
            if (empty($data[0])) continue;

            // Mapping Data Tracking (13 Kolom)
            $ukm   = trim($data[11] ?? '-');
            $match = trim($data[12] ?? 'Tidak Mengikuti UKM');

            // Fix jika kosong
            if ($ukm === '') $ukm = '-';
            if ($match === '') $match = 'Tidak Mengikuti UKM';

            // Masukkan ke DB
            TalentPool::create([
                'nim'              => trim($data[0] ?? '-'),
                'nama'             => trim($data[1] ?? '-'),
                'fakultas'         => $this->fixFakultas(trim($data[2] ?? ''), trim($data[3] ?? '')),
                'jurusan'          => trim($data[3] ?? '-'),
                'angkatan'         => trim($data[4] ?? '-'),
                'nama_lomba'       => trim($data[5] ?? '-'),
                'hasil_lomba'      => trim($data[6] ?? '-'),
                'tahun_lomba'      => strtok(trim($data[7] ?? '-'), '.'),
                'tingkat_lomba'    => trim($data[8] ?? '-'),
                'tipe_lomba'       => trim($data[9] ?? '-'),
                'kategori_ukm'     => trim($data[10] ?? '-'),
                'ukm_yang_diikuti' => $ukm,    // <--- INI PENTING
                'match_or_not'     => $match,  // <--- INI PENTING
            ]);
            $count++;
        }
        fclose($handle);

        $this->command->info("🎉 SUKSES: $count Data Masuk.");
    }

    private function fixFakultas($fakultas, $jurusan) {
        if (!empty($fakultas) && $fakultas != '-') return $fakultas;
        $j = strtoupper(trim($jurusan));
        if (in_array($j, ['ACC', 'MA', 'EC', 'MKT', 'SBM', 'IBM', 'PA', 'HT'])) return 'FBE (Bisnis & Ekonomika)';
        if (in_array($j, ['IF', 'INF', 'SI', 'IND', 'TE', 'TM', 'TK', 'MIE'])) return 'Teknik';
        if (in_array($j, ['VCD', 'DES', 'FPD', 'DKV', 'KI'])) return 'FIK (Industri Kreatif)';
        if (in_array($j, ['FARM', 'BIO', 'KIM'])) return 'Farmasi';
        if (in_array($j, ['PSI', 'PSY'])) return 'Psikologi';
        if (in_array($j, ['HUK', 'HK'])) return 'Hukum';
        if (in_array($j, ['KED', 'DR'])) return 'Kedokteran';
        return 'Fakultas Lainnya';
    }
}
