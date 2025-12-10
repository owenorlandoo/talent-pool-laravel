<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komparasi Data - Talent Pool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .select-box { max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; background: white; }

        /* Styling Tab Custom */
        .nav-tabs .nav-link { color: #6c757d; font-weight: 600; border: none; border-bottom: 3px solid transparent; }
        .nav-tabs .nav-link.active { color: #0d6efd; background: transparent; border-bottom: 3px solid #0d6efd; }
        .nav-tabs .nav-link:hover { color: #0d6efd; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Dashboard
            </a>
            <span class="navbar-text text-white fw-bold"><i class="bi bi-bar-chart-steps me-2"></i> Mode Komparasi</span>
        </div>
    </nav>

    <div class="container pb-5">

        <ul class="nav nav-tabs mb-4 border-bottom-0 justify-content-center" id="compareTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'jurusan' ? 'active' : '' }} fs-5 px-4" id="jurusan-tab" data-bs-toggle="tab" data-bs-target="#jurusan-pane" type="button" role="tab">
                    <i class="bi bi-diagram-2-fill me-2"></i> Antar Jurusan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'fakultas' ? 'active' : '' }} fs-5 px-4" id="fakultas-tab" data-bs-toggle="tab" data-bs-target="#fakultas-pane" type="button" role="tab">
                    <i class="bi bi-building-fill me-2"></i> Antar Fakultas
                </button>
            </li>
        </ul>

        <div class="tab-content" id="compareTabContent">

            <div class="tab-pane fade {{ $activeTab == 'jurusan' ? 'show active' : '' }}" id="jurusan-pane" role="tabpanel">
                <div class="card mb-4 border-primary border-top border-4">
                    <div class="card-body bg-light">
                        <form action="{{ url('/compare') }}" method="GET">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-check2-square"></i> Pilih Jurusan:</h6>
                            <div class="select-box mb-3">
                                <div class="row">
                                    @foreach($allJurusan as $j)
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jurusan[]" value="{{ $j }}" id="j_{{ $j }}" {{ in_array($j, $selectedJurusan) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="j_{{ $j }}">{{ $j }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ url('/compare') }}" class="btn btn-outline-secondary btn-sm me-2">Reset</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-left-right"></i> Bandingkan Jurusan</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(count($selectedJurusan) > 0)
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white fw-bold text-center">Statistik Match per Jurusan</div>
                                <div class="card-body">
                                    <div style="height: 350px;"><canvas id="chartJurusan"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white fw-bold text-center">Insight Minat</div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0 align-middle small">
                                        <thead class="table-dark"><tr><th>Jurusan</th><th>Dominasi Lomba</th></tr></thead>
                                        <tbody>
                                            @foreach($topMinatJur as $nama => $minat)
                                            <tr><td class="fw-bold">{{ $nama }}</td><td><span class="badge bg-secondary">{{ $minat }}</span></td></tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="tab-pane fade {{ $activeTab == 'fakultas' ? 'show active' : '' }}" id="fakultas-pane" role="tabpanel">
                <div class="card mb-4 border-success border-top border-4">
                    <div class="card-body bg-light">
                        <form action="{{ url('/compare') }}" method="GET">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-check2-circle"></i> Pilih Fakultas:</h6>
                            <div class="select-box mb-3">
                                <div class="row">
                                    @foreach($allFakultas as $f)
                                    <div class="col-md-4 col-12 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="fakultas[]" value="{{ $f }}" id="f_{{ $f }}" {{ in_array($f, $selectedFakultas) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="f_{{ $f }}">{{ $f }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ url('/compare') }}" class="btn btn-outline-secondary btn-sm me-2">Reset</a>
                                <button type="submit" class="btn btn-success"><i class="bi bi-arrow-left-right"></i> Bandingkan Fakultas</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(count($selectedFakultas) > 0)
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white fw-bold text-center">Statistik Match per Fakultas</div>
                                <div class="card-body">
                                    <div style="height: 350px;"><canvas id="chartFakultas"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white fw-bold text-center">Insight Minat</div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0 align-middle small">
                                        <thead class="table-dark"><tr><th>Fakultas</th><th>Dominasi Lomba</th></tr></thead>
                                        <tbody>
                                            @foreach($topMinatFak as $nama => $minat)
                                            <tr><td class="fw-bold">{{ $nama }}</td><td><span class="badge bg-secondary">{{ $minat }}</span></td></tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div> </div>

    <script>
        // === 1. CHART JURUSAN ===
        const labelsJur = @json($labelsJur);
        if (labelsJur.length > 0) {
            new Chart(document.getElementById('chartJurusan'), {
                type: 'bar',
                data: {
                    labels: labelsJur,
                    datasets: [
                        { label: 'Match', data: @json($dataMatchJur), backgroundColor: '#0d6efd' },
                        { label: 'No Match', data: @json($dataNoMatchJur), backgroundColor: '#ffc107' },
                        { label: 'Tidak Ikut', data: @json($dataTidakIkutJur), backgroundColor: '#6c757d' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { x: { stacked: true }, y: { stacked: true } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        // === 2. CHART FAKULTAS ===
        const labelsFak = @json($labelsFak);
        if (labelsFak.length > 0) {
            new Chart(document.getElementById('chartFakultas'), {
                type: 'bar',
                data: {
                    labels: labelsFak,
                    datasets: [
                        { label: 'Match', data: @json($dataMatchFak), backgroundColor: '#198754' },
                        { label: 'No Match', data: @json($dataNoMatchFak), backgroundColor: '#ffc107' },
                        { label: 'Tidak Ikut', data: @json($dataTidakIkutFak), backgroundColor: '#6c757d' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { x: { stacked: true }, y: { stacked: true } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
