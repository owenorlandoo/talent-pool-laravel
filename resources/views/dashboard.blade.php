<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talent Analytics Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* Styling Tab Navigasi */
        .nav-pills .nav-link {
            color: #555;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.9rem;
            margin-right: 5px;
            background: white;
            border: 1px solid #ddd;
            transition: all 0.2s;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
            font-weight: bold;
        }
        .nav-pills .nav-link:hover:not(.active) { background-color: #e9ecef; }

        /* Container Chart */
        .chart-wrapper { position: relative; height: 400px; width: 100%; }

        /* Styling Tabel Lebar */
        .badge-fakultas { background-color: #6610f2; }
        .badge-jurusan { background-color: #0d6efd; }
        .table-responsive { overflow-x: auto; }
        .table { font-size: 0.85rem; white-space: nowrap; } /* Agar tabel rapi 1 baris */
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="bi bi-speedometer2 me-2 text-warning"></i> Talent Analytics
            </a>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Update Data
                </button>

                <a href="{{ url('/compare') }}" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bi bi-bar-chart-steps me-1"></i> Mode Komparasi
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-3">
                <form action="{{ url('/') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-auto"><span class="fw-bold text-secondary"><i class="bi bi-funnel-fill"></i> Filter:</span></div>

                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama/NIM..." value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <select name="fakultas" class="form-select form-select-sm">
                                <option value="">Semua Fakultas</option>
                                @foreach($listFakultas as $f) <option value="{{ $f }}" {{ request('fakultas') == $f ? 'selected' : '' }}>{{ $f }}</option> @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="jurusan" class="form-select form-select-sm">
                                <option value="">Semua Jurusan</option>
                                @foreach($listJurusan as $j) <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>{{ $j }}</option> @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="Match" {{ request('status') == 'Match' ? 'selected' : '' }}>Match</option>
                                <option value="No Match" {{ request('status') == 'No Match' ? 'selected' : '' }}>No Match</option>
                                <option value="Tidak Mengikuti UKM" {{ request('status') == 'Tidak Mengikuti UKM' ? 'selected' : '' }}>Tidak Mengikuti UKM</option>
                            </select>
                        </div>

                        <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i> Cari</button></div>
                        <div class="col-md-1"><a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a></div>
                    </div>
                </form>
            </div>
        </div>

        @if($students->total() > 0)
        <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item"><button class="nav-link active" onclick="showChart('fakultas', this)"><i class="bi bi-building"></i> Distribusi Fakultas</button></li>
            <li class="nav-item"><button class="nav-link" onclick="showChart('lomba', this)"><i class="bi bi-trophy"></i> Topik Lomba</button></li>
            <li class="nav-item"><button class="nav-link" onclick="showChart('match', this)"><i class="bi bi-pie-chart"></i> Analisis Match</button></li>
            <li class="nav-item"><button class="nav-link" onclick="showChart('relasi', this)"><i class="bi bi-diagram-3"></i> Fakultas vs Minat</button></li>
            <li class="nav-item"><button class="nav-link" onclick="showChart('angkatan', this)"><i class="bi bi-calendar-range"></i> Tren Angkatan</button></li>
            <li class="nav-item"><button class="nav-link" onclick="showChart('jurusan', this)"><i class="bi bi-diagram-2"></i> Analisis Jurusan</button></li>
        </ul>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-center fw-bold mb-4" id="chartTitle">Distribusi Partisipasi per Fakultas</h5>
                <div class="chart-wrapper">
                    <canvas id="mainChart"></canvas>
                </div>
                <div class="alert alert-light mt-3 text-center small text-muted fst-italic" id="chartDesc">
                    Menampilkan fakultas mana yang memiliki mahasiswa paling aktif dalam kegiatan.
                </div>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary"><i class="bi bi-table"></i> Data Detail Mahasiswa</span>
                <span class="badge bg-secondary">Total: {{ $students->total() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Fakultas</th>
                                <th>Jurusan</th>
                                <th>Angkatan</th>
                                <th>Nama Lomba</th>
                                <th>Hasil</th>
                                <th>Tahun</th>
                                <th>Tingkat</th>
                                <th>Tipe Lomba</th>
                                <th>Kategori UKM</th>
                                <th>UKM Yang Diikuti</th>
                                <th>MATCH OR NOT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $s)
                            <tr>
                                <td class="text-center">{{ $s->nim }}</td>
                                <td class="fw-bold">{{ $s->nama }}</td>
                                <td class="text-center">{{ $s->fakultas }}</td>
                                <td class="text-center">{{ $s->jurusan }}</td>
                                <td class="text-center">{{ $s->angkatan }}</td>

                                <td>{{ $s->nama_lomba }}</td>
                                <td class="text-center">{{ $s->hasil_lomba }}</td>
                                <td class="text-center">{{ $s->tahun_lomba }}</td>
                                <td class="text-center">{{ $s->tingkat_lomba }}</td>
                                <td>{{ $s->tipe_lomba }}</td>
                                <td>{{ $s->kategori_ukm }}</td>

                                <td>
                                    @if($s->ukm_yang_diikuti == '-' || empty($s->ukm_yang_diikuti))
                                        <span class="text-muted fst-italic">-</span>
                                    @else
                                        {{ $s->ukm_yang_diikuti }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->match_or_not == 'Match')
                                        <span class="badge bg-success">MATCH</span>
                                    @elseif($s->match_or_not == 'No Match')
                                        <span class="badge bg-warning text-dark">NO MATCH</span>
                                    @else
                                        <span class="badge bg-secondary">TIDAK IKUT</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-center py-2">
                {{ $students->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-upload"></i> Update Data Talent Pool</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">
                            Upload file CSV terbaru. Data lama akan dihapus dan diganti dengan data baru.
                            Sistem akan otomatis memperbaiki nama fakultas yang kosong dan melakukan matching UKM.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File CSV</label>
                            <input type="file" name="csv_file" class="form-control" required accept=".csv">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Upload & Proses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show bg-success text-white" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Berhasil!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif

    <script>
        // === 1. TERIMA DATA DARI CONTROLLER ===
        const dFakultas = @json($chartFakultas ?? []);
        const dLomba = @json($chartLomba ?? []);
        const dStatus = @json($chartStatus ?? []);
        const dAngkatan = @json($chartAngkatan ?? []);
        const dJurusan = @json($chartJurusan ?? []);
        const dRelasi = @json($relasiData ?? []);

        let currentChart = null; // Menyimpan instance chart aktif

        document.addEventListener("DOMContentLoaded", function() {
            // Render chart default (Fakultas) jika data ada
            if (Object.keys(dFakultas).length > 0) {
                renderChart('bar', Object.keys(dFakultas), Object.values(dFakultas), 'Mahasiswa', '#4e73df');
            }
        });

        // === 2. FUNGSI GANTI TAMPILAN (SWITCH CHART) ===
        function showChart(type, btn) {
            // Update tombol aktif
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');

            // Hapus chart lama
            if (currentChart) currentChart.destroy();

            const title = document.getElementById('chartTitle');
            const desc = document.getElementById('chartDesc');

            // Logic Pilihan
            if (type === 'fakultas') {
                title.innerText = "Distribusi Partisipasi per Fakultas";
                desc.innerText = "Melihat fakultas mana yang paling aktif.";
                renderChart('bar', Object.keys(dFakultas), Object.values(dFakultas), 'Total', '#4e73df');
            }
            else if (type === 'lomba') {
                title.innerText = "Topik Lomba Terpopuler";
                desc.innerText = "Jenis kompetisi yang paling banyak diminati mahasiswa.";
                renderChart('bar', Object.keys(dLomba), Object.values(dLomba), 'Peminat', '#36b9cc', 'y'); // Horizontal
            }
            else if (type === 'match') {
                title.innerText = "Analisis Kesesuaian (Match)";
                desc.innerText = "Proporsi mahasiswa yang UKM-nya relevan dengan lomba mereka.";
                renderDoughnut(Object.keys(dStatus), Object.values(dStatus));
            }
            else if (type === 'angkatan') {
                title.innerText = "Tren Partisipasi per Angkatan";
                desc.innerText = "Melihat keaktifan mahasiswa dari tahun ke tahun.";
                renderChart('line', Object.keys(dAngkatan), Object.values(dAngkatan), 'Jumlah', '#f6c23e');
            }
            else if (type === 'jurusan') {
                title.innerText = "Analisis Jurusan (Top 15 Prodi)";
                desc.innerText = "Drill-down untuk melihat prodi mana yang paling berprestasi.";
                renderChart('bar', Object.keys(dJurusan), Object.values(dJurusan), 'Total', '#e74a3b');
            }
            else if (type === 'relasi') {
                title.innerText = "Hubungan Fakultas dengan Minat";
                desc.innerText = "Dominasi kategori lomba pada setiap fakultas.";
                renderStackedBar(dRelasi);
            }
        }

        // === 3. FUNGSI RENDER CHART UMUM ===
        function renderChart(type, labels, data, labelName, color, indexAxis = 'x') {
            const ctx = document.getElementById('mainChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelName,
                        data: data,
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 1,
                        fill: type === 'line' ? false : true,
                        tension: 0.3 // Lengkungan garis untuk line chart
                    }]
                },
                options: {
                    indexAxis: indexAxis,
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        }

        // === 4. FUNGSI RENDER DOUGHNUT ===
        function renderDoughnut(labels, data) {
            const ctx = document.getElementById('mainChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#198754', '#ffc107', '#6c757d', '#0dcaf0'],
                    }]
                },
                options: { maintainAspectRatio: false }
            });
        }

        // === 5. FUNGSI RENDER STACKED BAR (RELASI) ===
        function renderStackedBar(rawData) {
            const ctx = document.getElementById('mainChart').getContext('2d');

            const faculties = Object.keys(rawData);
            const allTypes = new Set();
            faculties.forEach(fak => Object.keys(rawData[fak]).forEach(type => allTypes.add(type)));
            const types = Array.from(allTypes);

            const datasets = types.map((type, index) => {
                const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];
                return {
                    label: type,
                    data: faculties.map(fak => rawData[fak][type] || 0),
                    backgroundColor: colors[index % colors.length]
                };
            });

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: faculties, datasets: datasets },
                options: {
                    maintainAspectRatio: false,
                    scales: { x: { stacked: true }, y: { stacked: true } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
