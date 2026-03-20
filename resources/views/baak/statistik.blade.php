<x-baak-layout title="Statistik">

{{-- ── Header ─────────────────────────────────────────────────── --}}
<div class="px-6 py-5 border-b border-gray-200 bg-white">
    <h1 class="text-2xl font-bold text-gray-800">📊 Statistik & Analitik</h1>
    <p class="text-sm text-gray-500 mt-1">Visualisasi data jadwal perkuliahan, beban dosen, dan utilisasi ruangan</p>
</div>

{{-- ── Summary Cards ──────────────────────────────────────────── --}}
<div class="px-6 pt-5">
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $summary->totalJadwal }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Jadwal</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $summary->totalDosen }}</p>
            <p class="text-xs text-gray-500 mt-1">Dosen Aktif</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $summary->totalMatakuliah }}</p>
            <p class="text-xs text-gray-500 mt-1">Mata Kuliah</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $summary->totalRuangan }}</p>
            <p class="text-xs text-gray-500 mt-1">Ruangan</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-rose-600">{{ $summary->totalKelas }}</p>
            <p class="text-xs text-gray-500 mt-1">Kelas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-teal-600">{{ $summary->totalKrs }}</p>
            <p class="text-xs text-gray-500 mt-1">KRS Aktif</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $summary->totalSks }}</p>
            <p class="text-xs text-gray-500 mt-1">Total SKS</p>
        </div>
    </div>
</div>

{{-- ── Charts Row 1: Jadwal per Hari + Prodi ─────────────────── --}}
<div class="px-6 pt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Bar Chart: Distribusi Jadwal per Hari --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">📅 Distribusi Jadwal per Hari</h3>
        <div style="height: 180px"><canvas id="chartHari"></canvas></div>
    </div>

    {{-- Pie Chart: Distribusi per Prodi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">🎓 Distribusi per Program Studi</h3>
        <div style="height: 180px"><canvas id="chartProdi"></canvas></div>
    </div>
</div>

{{-- ── Charts Row 2: Beban Dosen + Ruangan ────────────────────── --}}
<div class="px-6 pt-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Horizontal Bar: Beban Mengajar Dosen --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">👨‍🏫 Top 10 Beban Mengajar Dosen</h3>
        <div style="height: 200px"><canvas id="chartDosen"></canvas></div>
    </div>

    {{-- Doughnut: Utilisasi Ruangan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">🏫 Utilisasi Ruangan</h3>
        <div style="height: 160px"><canvas id="chartRuangan"></canvas></div>
        <div class="mt-2 text-center text-xs text-gray-500">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1"></span> Terpakai ({{ $ruanganTerpakai }})
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-300 ml-3 mr-1"></span> Kosong ({{ $ruanganKosong }})
        </div>
    </div>
</div>

{{-- ── Charts Row 3: SKS + Laporan ────────────────────────────── --}}
<div class="px-6 pt-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Distribusi SKS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">📚 Distribusi SKS</h3>
        <div style="height: 170px"><canvas id="chartSks"></canvas></div>
    </div>

    {{-- Laporan Kehadiran --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-2">📋 Status Laporan Kehadiran</h3>
        <div style="height: 150px"><canvas id="chartLaporan"></canvas></div>
        <div class="mt-2 flex justify-center gap-3 text-xs text-gray-500">
            <span><span class="inline-block w-2.5 h-2.5 rounded-full bg-yellow-400 mr-1"></span>Pending ({{ $laporanStats->pending }})</span>
            <span><span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-1"></span>Valid ({{ $laporanStats->valid }})</span>
            <span><span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400 mr-1"></span>Ditolak ({{ $laporanStats->ditolak }})</span>
        </div>
    </div>

    {{-- Time Slot Heatmap --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-3">🕐 Kepadatan Jam Kuliah</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th class="text-left py-1 text-gray-500">Jam</th>
                        @foreach($hariOrder as $h)
                            <th class="text-center py-1 text-gray-500">{{ substr($h, 0, 3) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allHours = collect($timeSlots)->flatten(1)->keys()->merge(
                            collect($timeSlots)->flatMap(fn($slots) => array_keys($slots))
                        )->unique()->sort()->values();
                        if($allHours->isEmpty()) $allHours = collect(['07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00']);
                        $maxVal = collect($timeSlots)->flatMap(fn($s) => $s)->max() ?: 1;
                    @endphp
                    @foreach($allHours as $hour)
                    <tr>
                        <td class="py-1 font-mono text-gray-600">{{ $hour }}</td>
                        @foreach($hariOrder as $h)
                            @php $val = $timeSlots[$h][$hour] ?? 0; @endphp
                            <td class="text-center py-1">
                                @if($val > 0)
                                    <span class="inline-block w-7 h-7 rounded leading-7 text-white font-bold"
                                          style="background-color: rgba(16,185,129, {{ 0.3 + (0.7 * $val / $maxVal) }})">
                                        {{ $val }}
                                    </span>
                                @else
                                    <span class="inline-block w-7 h-7 rounded leading-7 bg-gray-100 text-gray-300">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Table: Detail Utilisasi Ruangan ────────────────────────── --}}
<div class="px-6 py-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-3">🏫 Detail Utilisasi Ruangan</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-gray-500 text-xs uppercase">
                        <th class="py-2 px-3">No</th>
                        <th class="py-2 px-3">Kode</th>
                        <th class="py-2 px-3">Nama Ruangan</th>
                        <th class="py-2 px-3 text-center">Jumlah Jadwal</th>
                        <th class="py-2 px-3 text-center">Total Jam/Minggu</th>
                        <th class="py-2 px-3">Tingkat Utilisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilisasiRuangan as $idx => $r)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2 px-3 text-gray-400">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3 font-semibold text-gray-700">{{ $r['kode'] }}</td>
                        <td class="py-2 px-3 text-gray-600">{{ $r['nama'] }}</td>
                        <td class="py-2 px-3 text-center">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $r['jumlah'] }}</span>
                        </td>
                        <td class="py-2 px-3 text-center">{{ number_format($r['jamTotal'], 1) }} jam</td>
                        <td class="py-2 px-3">
                            @php
                                $maxSlots = 36; // 6 hari × 6 slot
                                $pct = min(100, round(($r['jumlah'] / $maxSlots) * 100));
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $pct >= 70 ? 'bg-red-500' : ($pct >= 40 ? 'bg-yellow-500' : 'bg-emerald-500') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-4 text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        emerald: 'rgba(16,185,129,0.8)',
        blue: 'rgba(59,130,246,0.8)',
        purple: 'rgba(139,92,246,0.8)',
        orange: 'rgba(249,115,22,0.8)',
        rose: 'rgba(244,63,94,0.8)',
        teal: 'rgba(20,184,166,0.8)',
        indigo: 'rgba(99,102,241,0.8)',
    };
    const bgColors = [colors.emerald, colors.blue, colors.purple, colors.orange, colors.rose, colors.teal, colors.indigo];

    // ── 1. Jadwal per Hari ──
    new Chart(document.getElementById('chartHari'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($jadwalPerHari)) !!},
            datasets: [{
                label: 'Jumlah Jadwal',
                data: {!! json_encode(array_values($jadwalPerHari)) !!},
                backgroundColor: bgColors,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });

    // ── 2. Distribusi per Prodi ──
    const prodiData = @json($jadwalPerProdi);
    new Chart(document.getElementById('chartProdi'), {
        type: 'doughnut',
        data: {
            labels: prodiData.map(d => d.prodi),
            datasets: [{
                data: prodiData.map(d => d.jumlah),
                backgroundColor: bgColors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } }
            }
        }
    });

    // ── 3. Beban Dosen ──
    const dosenData = @json($bebanDosen);
    new Chart(document.getElementById('chartDosen'), {
        type: 'bar',
        data: {
            labels: dosenData.map(d => d.nama.length > 20 ? d.nama.substring(0, 20) + '...' : d.nama),
            datasets: [
                {
                    label: 'Jumlah Sesi',
                    data: dosenData.map(d => d.jumlah),
                    backgroundColor: colors.emerald,
                    borderRadius: 4,
                },
                {
                    label: 'Total Jam',
                    data: dosenData.map(d => Math.round(d.totalJam * 10) / 10),
                    backgroundColor: colors.blue,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 10 }, boxWidth: 10 } }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                y: { ticks: { font: { size: 10 } } }
            }
        }
    });

    // ── 4. Utilisasi Ruangan ──
    new Chart(document.getElementById('chartRuangan'), {
        type: 'doughnut',
        data: {
            labels: ['Terpakai', 'Kosong'],
            datasets: [{
                data: [{{ $ruanganTerpakai }}, {{ $ruanganKosong }}],
                backgroundColor: [colors.emerald, 'rgba(209,213,219,0.8)'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // ── 5. Distribusi SKS ──
    const sksData = @json($sksDistribusi);
    new Chart(document.getElementById('chartSks'), {
        type: 'polarArea',
        data: {
            labels: sksData.map(d => d.sks),
            datasets: [{
                data: sksData.map(d => d.jumlah),
                backgroundColor: bgColors.map(c => c.replace('0.8', '0.6')),
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } }
            }
        }
    });

    // ── 6. Laporan Kehadiran ──
    new Chart(document.getElementById('chartLaporan'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Valid', 'Ditolak'],
            datasets: [{
                data: [{{ $laporanStats->pending }}, {{ $laporanStats->valid }}, {{ $laporanStats->ditolak }}],
                backgroundColor: ['rgba(250,204,21,0.8)', 'rgba(34,197,94,0.8)', 'rgba(248,113,113,0.8)'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endpush

</x-baak-layout>
