<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>📺 Papan Jadwal Live — JadwalKuliah</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; background: #f0fdf4; }
        .ticker-wrap { overflow: hidden; }
        .ticker-text {
            display: inline-block;
            animation: ticker 30s linear infinite;
            white-space: nowrap;
        }
        @keyframes ticker {
            0% { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }
        .live-row-berlangsung { background: #ecfdf5; }
        .live-row-akan { background: #f0fdfa; }
        .live-row-selesai { background: #f8fafc; color: #64748b; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #d1fae5; }
        ::-webkit-scrollbar-thumb { background: #34d399; border-radius: 999px; }
    </style>
</head>
<body class="min-h-screen text-gray-800">

<header class="bg-white border-b border-emerald-200 shadow-sm px-6 py-4 sticky top-0 z-50">
    <div class="max-w-screen-2xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-3xl">🎓</span>
            <div>
                <h1 class="font-black text-lg text-emerald-800 leading-none">PAPAN JADWAL KULIAH</h1>
                <p class="text-xs text-emerald-500 mt-0.5">Format jadwal live disamakan dengan input BAAK</p>
            </div>
        </div>

        <div class="text-center">
            <div id="live-clock" class="font-mono font-black text-3xl text-emerald-600 tabular-nums">--:--:--</div>
            <div id="live-tanggal" class="text-xs text-gray-500 mt-0.5">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
        </div>

        <div class="text-right">
            <div class="text-sm text-gray-600">Hari ini: <span id="live-hari" class="font-bold text-emerald-700">{{ $hariIni ?: 'Minggu' }}</span></div>
            <div class="flex items-center gap-1.5 justify-end mt-1">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-xs text-emerald-600 font-medium">LIVE</span>
                <span class="text-xs text-gray-400 ml-1">— auto-refresh tiap 30 detik</span>
            </div>
        </div>
    </div>
</header>

<div class="bg-emerald-600 py-1.5 ticker-wrap">
    <div class="ticker-text text-xs text-white font-medium px-4" id="ticker-content">
        @if($berlangsung->count() > 0)
            @foreach($berlangsung as $item)
                🟢 SEDANG BERLANGSUNG: {{ $item['kode'] }} — {{ $item['matakuliah'] }} — {{ $item['dosen'] }} — Ruang {{ $item['ruang'] }}
                &nbsp;&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;&nbsp;
            @endforeach
        @else
            📭 Tidak ada kelas yang sedang berlangsung saat ini — papan live akan otomatis diperbarui.
        @endif
    </div>
</div>

<div class="bg-white border-b border-emerald-100 shadow-sm px-6 py-2">
    <div class="max-w-screen-2xl mx-auto flex items-center gap-6 text-sm">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
            <span class="text-gray-500">Berlangsung:</span>
            <span id="count-berlangsung" class="font-bold text-emerald-600">{{ $berlangsung->count() }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-teal-400 rounded-full"></span>
            <span class="text-gray-500">Akan Datang:</span>
            <span id="count-akan" class="font-bold text-teal-600">{{ $akanDatang->count() }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-slate-300 rounded-full"></span>
            <span class="text-gray-500">Selesai:</span>
            <span id="count-selesai" class="font-bold text-slate-500">{{ $selesai->count() }}</span>
        </div>
        <div class="ml-auto flex items-center gap-2 text-xs text-gray-400">
            <span>Update terakhir:</span>
            <span id="last-update" class="font-mono text-gray-500">{{ now()->format('H:i:s') }}</span>
        </div>
    </div>
</div>

<main class="max-w-screen-2xl mx-auto px-6 py-6 pb-16 space-y-6">

    {{-- ─── BAGIAN 1: Jadwal Kuliah Hari Ini ─────────────────────────────── --}}
    <section>
        <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📋</span>
                    <div>
                        <h2 class="font-bold text-gray-900">Jadwal Kuliah Hari Ini</h2>
                        <p id="live-subtitle" class="text-xs text-gray-500">Seluruh jadwal perkuliahan — {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>
                </div>
                <span id="badge-total" class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                    {{ $jadwalHariIni->count() }} jadwal
                </span>
            </div>
            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 bg-emerald-50 border-b border-emerald-100">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">NO</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">SMT</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">HARI</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">WAKTU</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">RUANG</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">KODE</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700">MATA KULIAH</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700 whitespace-nowrap">SKS</th>
                            <th class="text-left px-4 py-3 font-semibold text-emerald-700">DOSEN</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-semua" class="divide-y divide-gray-100">
                        @forelse($jadwalHariIni as $item)
                        @php
                            $rowBg = match($item['status']) {
                                'berlangsung' => 'live-row-berlangsung',
                                'selesai'     => 'live-row-selesai',
                                default       => 'live-row-akan',
                            };
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-4 py-3 font-semibold text-gray-700">{{ $item['no'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item['smt'] }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $item['hari'] }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700 whitespace-nowrap">{{ $item['waktu'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item['ruang'] }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700">{{ $item['kode'] }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $item['matakuliah'] }}</div>
                                @if(!empty($item['kelas']))
                                <div class="text-xs text-gray-500 mt-0.5">{{ $item['kelas'] }}</div>
                                @endif
                                <div class="mt-1">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold
                                        {{ $item['status'] === 'berlangsung' ? 'bg-emerald-100 text-emerald-700' : ($item['status'] === 'akan_datang' ? 'bg-teal-100 text-teal-700' : 'bg-slate-200 text-slate-600') }}">
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $item['sks'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item['dosen'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                <div class="text-4xl mb-3">📭</div>
                                <p>Tidak ada jadwal kuliah untuk hari ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ─── BAGIAN 2 & 3: Berlangsung + Selesai ──────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- BAGIAN 2: Sedang Berlangsung --}}
        <section>
            <div class="bg-white rounded-3xl border border-emerald-200 shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-emerald-100 flex items-center justify-between bg-emerald-500">
                    <div class="flex items-center gap-3">
                        <span class="text-xl animate-pulse">🟢</span>
                        <div>
                            <h2 class="font-bold text-white">Sedang Berlangsung</h2>
                            <p class="text-xs text-emerald-100">Kelas yang aktif saat ini</p>
                        </div>
                    </div>
                    <span id="badge-berlangsung" class="text-xs px-3 py-1 rounded-full bg-white/20 text-white font-semibold">
                        {{ $berlangsung->count() }} kelas
                    </span>
                </div>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-emerald-50 border-b border-emerald-100">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold text-emerald-700 text-xs">WAKTU</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-emerald-700 text-xs">RUANG</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-emerald-700 text-xs">MATA KULIAH</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-emerald-700 text-xs">DOSEN</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-berlangsung" class="divide-y divide-emerald-50">
                            @forelse($berlangsung as $item)
                            <tr class="live-row-berlangsung">
                                <td class="px-4 py-3 font-mono text-emerald-700 text-sm font-medium whitespace-nowrap">{{ $item['waktu'] }}</td>
                                <td class="px-4 py-3 text-gray-700 font-semibold">{{ $item['ruang'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $item['matakuliah'] }}</div>
                                    @if(!empty($item['kelas']))
                                    <div class="text-xs text-gray-500">{{ $item['kelas'] }} · SMT {{ $item['smt'] }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $item['dosen'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-400">
                                    <div class="text-3xl mb-2">⏳</div>
                                    <p class="text-sm">Belum ada kelas yang berlangsung.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- BAGIAN 3: Kelas Selesai --}}
        <section>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">✅</span>
                        <div>
                            <h2 class="font-bold text-slate-700">Kelas Selesai</h2>
                            <p class="text-xs text-slate-500">Perkuliahan yang telah berakhir hari ini</p>
                        </div>
                    </div>
                    <span id="badge-selesai" class="text-xs px-3 py-1 rounded-full bg-slate-200 text-slate-600 font-semibold">
                        {{ $selesai->count() }} kelas
                    </span>
                </div>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-600 text-xs">WAKTU</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-600 text-xs">RUANG</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-600 text-xs">MATA KULIAH</th>
                                <th class="text-left px-4 py-2.5 font-semibold text-slate-600 text-xs">DOSEN</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-selesai" class="divide-y divide-slate-50">
                            @forelse($selesai as $item)
                            <tr class="live-row-selesai">
                                <td class="px-4 py-3 font-mono text-slate-400 text-sm whitespace-nowrap">{{ $item['waktu'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $item['ruang'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-600 line-through decoration-slate-300">{{ $item['matakuliah'] }}</div>
                                    @if(!empty($item['kelas']))
                                    <div class="text-xs text-slate-400">{{ $item['kelas'] }} · SMT {{ $item['smt'] }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $item['dosen'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-400">
                                    <div class="text-3xl mb-2">🎓</div>
                                    <p class="text-sm">Belum ada kelas yang selesai hari ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</main>

<footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-emerald-200 shadow-sm px-6 py-2 z-40">
    <div class="max-w-screen-2xl mx-auto flex items-center justify-between text-xs text-gray-500">
        <span>🎓 JadwalKuliah — Sistem Informasi Penjadwalan Perkuliahan</span>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium transition">Login Portal</a>
            <span>•</span>
            <span class="text-gray-400">Powered by INSUN AI ✨</span>
        </div>
    </div>
</footer>

{{-- INSUN AI Chatbot --}}
<x-insun-chat />

<script src="{{ mix('js/app.js') }}"></script>
<script>
let currentHari = '{{ $hariIni }}';

function updateClock() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('live-clock').textContent =
        pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
}

function statusClass(status) {
    if (status === 'berlangsung') return 'live-row-berlangsung';
    if (status === 'akan_datang') return 'live-row-akan';
    return 'live-row-selesai';
}

function statusBadgeClass(status) {
    if (status === 'berlangsung') return 'bg-emerald-100 text-emerald-700';
    if (status === 'akan_datang') return 'bg-teal-100 text-teal-700';
    return 'bg-slate-200 text-slate-600';
}

function renderSemua(list) {
    const tbody = document.getElementById('tbody-semua');
    if (!tbody) return;
    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">
            <div class="text-4xl mb-3">📭</div><p>Tidak ada jadwal kuliah untuk hari ini.</p></td></tr>`;
        return;
    }
    tbody.innerHTML = list.map(item => `
        <tr class="${statusClass(item.status)}">
            <td class="px-4 py-3 font-semibold text-gray-700">${item.no}</td>
            <td class="px-4 py-3 text-gray-600">${item.smt ?? '—'}</td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">${item.hari ?? '—'}</td>
            <td class="px-4 py-3 font-mono text-gray-700 whitespace-nowrap">${item.waktu ?? '—'}</td>
            <td class="px-4 py-3 text-gray-700">${item.ruang ?? '—'}</td>
            <td class="px-4 py-3 font-mono text-gray-700">${item.kode ?? '—'}</td>
            <td class="px-4 py-3">
                <div class="font-semibold text-gray-900">${item.matakuliah ?? '—'}</div>
                ${item.kelas ? `<div class="text-xs text-gray-500 mt-0.5">${item.kelas}</div>` : ''}
                <div class="mt-1">
                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold ${statusBadgeClass(item.status)}">
                        ${item.status_label ?? '—'}
                    </span>
                </div>
            </td>
            <td class="px-4 py-3 text-gray-700">${item.sks ?? '—'}</td>
            <td class="px-4 py-3 text-gray-700">${item.dosen ?? '—'}</td>
        </tr>`).join('');
}

function renderBerlangsung(list) {
    const tbody = document.getElementById('tbody-berlangsung');
    if (!tbody) return;
    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">
            <div class="text-3xl mb-2">⏳</div><p class="text-sm">Belum ada kelas yang berlangsung.</p></td></tr>`;
        return;
    }
    tbody.innerHTML = list.map(item => `
        <tr class="live-row-berlangsung">
            <td class="px-4 py-3 font-mono text-emerald-700 text-sm font-medium whitespace-nowrap">${item.waktu ?? '—'}</td>
            <td class="px-4 py-3 text-gray-700 font-semibold">${item.ruang ?? '—'}</td>
            <td class="px-4 py-3">
                <div class="font-semibold text-gray-900">${item.matakuliah ?? '—'}</div>
                ${item.kelas ? `<div class="text-xs text-gray-500">${item.kelas} · SMT ${item.smt ?? '—'}</div>` : ''}
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">${item.dosen ?? '—'}</td>
        </tr>`).join('');
}

function renderSelesai(list) {
    const tbody = document.getElementById('tbody-selesai');
    if (!tbody) return;
    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">
            <div class="text-3xl mb-2">🎓</div><p class="text-sm">Belum ada kelas yang selesai hari ini.</p></td></tr>`;
        return;
    }
    tbody.innerHTML = list.map(item => `
        <tr class="live-row-selesai">
            <td class="px-4 py-3 font-mono text-slate-400 text-sm whitespace-nowrap">${item.waktu ?? '—'}</td>
            <td class="px-4 py-3 text-slate-500">${item.ruang ?? '—'}</td>
            <td class="px-4 py-3">
                <div class="font-medium text-slate-600 line-through decoration-slate-300">${item.matakuliah ?? '—'}</div>
                ${item.kelas ? `<div class="text-xs text-slate-400">${item.kelas} · SMT ${item.smt ?? '—'}</div>` : ''}
            </td>
            <td class="px-4 py-3 text-slate-400 text-xs">${item.dosen ?? '—'}</td>
        </tr>`).join('');
}

function updateTicker(berlangsung) {
    const ticker = document.getElementById('ticker-content');

    if (!berlangsung.length) {
        ticker.textContent = '📭 Tidak ada kelas yang sedang berlangsung saat ini — papan live akan otomatis diperbarui.';
        return;
    }

    ticker.innerHTML = berlangsung.map(item =>
        `🟢 SEDANG BERLANGSUNG: ${item.kode} — ${item.matakuliah} — ${item.dosen} — Ruang ${item.ruang}`
    ).join('&nbsp;&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;&nbsp;');
}

async function refreshData() {
    try {
        const res = await fetch('{{ route("live.data") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            }
        });

        if (!res.ok) {
            return;
        }

        const data = await res.json();

        // Deteksi pergantian hari
        if (data.hari_ini && data.hari_ini !== currentHari) {
            currentHari = data.hari_ini;
            document.getElementById('live-hari').textContent = data.hari_ini;
            if (data.tanggal) {
                document.getElementById('live-tanggal').textContent = data.tanggal;
                document.getElementById('live-subtitle').textContent = 'Seluruh jadwal perkuliahan — ' + data.tanggal;
            }
        }

        document.getElementById('last-update').textContent = data.server_time;
        document.getElementById('count-berlangsung').textContent = data.berlangsung.length;
        document.getElementById('count-akan').textContent = data.akan_datang.length;
        document.getElementById('count-selesai').textContent = data.selesai.length;

        // Update section badges
        document.getElementById('badge-total').textContent = (data.jadwal_hari_ini || []).length + ' jadwal';
        document.getElementById('badge-berlangsung').textContent = data.berlangsung.length + ' kelas';
        document.getElementById('badge-selesai').textContent = data.selesai.length + ' kelas';

        // Render all 3 sections
        renderSemua(data.jadwal_hari_ini || []);
        renderBerlangsung(data.berlangsung || []);
        renderSelesai(data.selesai || []);
        updateTicker(data.berlangsung || []);
    } catch (error) {
        console.warn('Refresh gagal:', error);
    }
}

setInterval(updateClock, 1000);
setInterval(refreshData, 30000);
updateClock();
</script>
</body>
</html>
