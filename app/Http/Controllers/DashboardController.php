<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TalentPool;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = TalentPool::query();

        // --- 1. FILTERING ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nim', 'LIKE', "%{$search}%")
                  ->orWhere('nama_lomba', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('fakultas')) $query->where('fakultas', $request->fakultas);
        if ($request->filled('jurusan')) $query->where('jurusan', $request->jurusan);
        if ($request->filled('angkatan')) $query->where('angkatan', $request->angkatan);
        if ($request->filled('status')) $query->where('match_or_not', $request->status);

        $baseChartQuery = clone $query;

        // --- 2. DATA TABLE ---
        $students = $query->latest()->paginate(10)->withQueryString();

        // --- 3. DATA VISUALISASI ---
        $chartFakultas = (clone $baseChartQuery)->select('fakultas', DB::raw('count(*) as total'))->groupBy('fakultas')->orderByDesc('total')->pluck('total', 'fakultas');
        $chartLomba = (clone $baseChartQuery)->select('tipe_lomba', DB::raw('count(*) as total'))->groupBy('tipe_lomba')->orderByDesc('total')->limit(10)->pluck('total', 'tipe_lomba');
        $chartStatus = (clone $baseChartQuery)->select('match_or_not', DB::raw('count(*) as total'))->groupBy('match_or_not')->pluck('total', 'match_or_not');
        $chartAngkatan = (clone $baseChartQuery)->select('angkatan', DB::raw('count(*) as total'))->groupBy('angkatan')->orderBy('angkatan', 'asc')->pluck('total', 'angkatan');
        $chartJurusan = (clone $baseChartQuery)->select('jurusan', DB::raw('count(*) as total'))->groupBy('jurusan')->orderByDesc('total')->limit(15)->pluck('total', 'jurusan');

        // Relasi Fakultas vs Minat
        $rawRelasi = (clone $baseChartQuery)->select('fakultas', 'tipe_lomba', DB::raw('count(*) as total'))->groupBy('fakultas', 'tipe_lomba')->orderBy('fakultas')->get();
        $relasiData = [];
        foreach($rawRelasi as $row) {
            $relasiData[$row->fakultas][$row->tipe_lomba] = $row->total;
        }

        // --- 4. DROPDOWN ---
        $listFakultas = TalentPool::select('fakultas')->distinct()->orderBy('fakultas')->pluck('fakultas');
        $listJurusan  = TalentPool::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');
        $listAngkatan = TalentPool::select('angkatan')->distinct()->orderBy('angkatan')->pluck('angkatan');

        return view('dashboard', compact(
            'students', 'chartFakultas', 'chartLomba', 'chartStatus', 'chartAngkatan', 'chartJurusan', 'relasiData',
            'listFakultas', 'listJurusan', 'listAngkatan'
        ));
    }

    public function compare(Request $request)
    {
        // ==========================
        // 1. LOGIKA KOMPARASI JURUSAN
        // ==========================
        $allJurusan = TalentPool::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');
        $selectedJurusan = $request->input('jurusan', []);

        $labelsJur = [];
        $dataMatchJur = [];
        $dataNoMatchJur = [];
        $dataTidakIkutJur = [];
        $topMinatJur = [];

        if (!empty($selectedJurusan)) {
            sort($selectedJurusan);
            $labelsJur = $selectedJurusan;
            foreach ($selectedJurusan as $j) {
                // Statistik
                $stats = TalentPool::where('jurusan', $j)
                    ->select('match_or_not', DB::raw('count(*) as total'))
                    ->groupBy('match_or_not')
                    ->pluck('total', 'match_or_not');

                $dataMatchJur[] = $stats['Match'] ?? 0;
                $dataNoMatchJur[] = $stats['No Match'] ?? 0;
                $dataTidakIkutJur[] = $stats['Tidak Mengikuti UKM'] ?? 0;

                // Minat Terpopuler
                $top = TalentPool::where('jurusan', $j)
                    ->select('tipe_lomba', DB::raw('count(*) as total'))
                    ->groupBy('tipe_lomba')
                    ->orderByDesc('total')
                    ->first();
                $topMinatJur[$j] = $top ? $top->tipe_lomba : '-';
            }
        }

        // ==========================
        // 2. LOGIKA KOMPARASI FAKULTAS (BARU)
        // ==========================
        $allFakultas = TalentPool::select('fakultas')->distinct()->orderBy('fakultas')->pluck('fakultas');
        $selectedFakultas = $request->input('fakultas', []);

        $labelsFak = [];
        $dataMatchFak = [];
        $dataNoMatchFak = [];
        $dataTidakIkutFak = [];
        $topMinatFak = [];

        if (!empty($selectedFakultas)) {
            sort($selectedFakultas);
            $labelsFak = $selectedFakultas;
            foreach ($selectedFakultas as $f) {
                // Statistik
                $stats = TalentPool::where('fakultas', $f)
                    ->select('match_or_not', DB::raw('count(*) as total'))
                    ->groupBy('match_or_not')
                    ->pluck('total', 'match_or_not');

                $dataMatchFak[] = $stats['Match'] ?? 0;
                $dataNoMatchFak[] = $stats['No Match'] ?? 0;
                $dataTidakIkutFak[] = $stats['Tidak Mengikuti UKM'] ?? 0;

                // Minat Terpopuler
                $top = TalentPool::where('fakultas', $f)
                    ->select('tipe_lomba', DB::raw('count(*) as total'))
                    ->groupBy('tipe_lomba')
                    ->orderByDesc('total')
                    ->first();
                $topMinatFak[$f] = $top ? $top->tipe_lomba : '-';
            }
        }

        // Tentukan Tab mana yang aktif saat halaman dimuat
        $activeTab = count($selectedFakultas) > 0 ? 'fakultas' : 'jurusan';

        return view('compare', compact(
            'activeTab',
            // Data Jurusan
            'allJurusan', 'selectedJurusan', 'labelsJur', 'dataMatchJur', 'dataNoMatchJur', 'dataTidakIkutJur', 'topMinatJur',
            // Data Fakultas
            'allFakultas', 'selectedFakultas', 'labelsFak', 'dataMatchFak', 'dataNoMatchFak', 'dataTidakIkutFak', 'topMinatFak'
        ));
    }

    // --- FUNGSI IMPORT YANG SUDAH DIPERBAIKI ---
    public function importData(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // Bersihkan data lama
        TalentPool::truncate();

        $handle = fopen($path, "r");

        // Deteksi Delimiter
        $line = fgets($handle);
        $delimiter = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
        rewind($handle);

        $firstRow = true;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if ($firstRow) { $firstRow = false; continue; }
            if (empty($data[0])) continue; // Skip baris kosong

            // Mapping Data (Sesuai Seeder)
            $nim          = trim($data[0] ?? '-');
            $nama         = trim($data[1] ?? '-');
            $fakultas     = trim($data[2] ?? '-');
            $jurusan      = trim($data[3] ?? '-');
            $angkatan     = trim($data[4] ?? '-');
            $nama_lomba   = trim($data[5] ?? '-');
            $hasil_lomba  = trim($data[6] ?? '-');
            $tahun_lomba  = strtok(trim($data[7] ?? '-'), '.'); // Hapus .0
            $tingkat      = trim($data[8] ?? '-');
            $tipe         = trim($data[9] ?? '-');
            $kategori     = trim($data[10] ?? '-');

            // --- AMBIL KOLOM MATCH & UKM DARI CSV (JANGAN HITUNG ULANG) ---
            $ukm          = trim($data[11] ?? '-');
            $match        = trim($data[12] ?? '-');

            // Logika Perbaikan Data Kosong
            if (empty($fakultas) || $fakultas == '-') {
                $fakultas = $this->getFakultasByJurusan($jurusan);
            }
            if (empty($ukm)) $ukm = '-';
            if (empty($match)) $match = 'Tidak Mengikuti UKM';

            TalentPool::create([
                'nim' => $nim,
                'nama' => $nama,
                'fakultas' => $fakultas,
                'jurusan' => $jurusan,
                'angkatan' => $angkatan,
                'nama_lomba' => $nama_lomba,
                'hasil_lomba' => $hasil_lomba,
                'tahun_lomba' => $tahun_lomba,
                'tingkat_lomba' => $tingkat,
                'tipe_lomba' => $tipe,
                'kategori_ukm' => $kategori,
                'ukm_yang_diikuti' => $ukm,
                'match_or_not' => $match,
            ]);
        }
        fclose($handle);

        return redirect('/')->with('success', 'Data Berhasil Diperbarui!');
    }

    private function getFakultasByJurusan($jurusan) {
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
