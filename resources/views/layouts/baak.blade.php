<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BAAK' }} — JadwalKuliah</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
    </style>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
    <aside class="w-60 flex-shrink-0 bg-emerald-900 text-white flex flex-col shadow-xl">
        <div class="px-5 py-4 border-b border-emerald-700">
            <span class="text-xl font-bold tracking-tight">🎓 JadwalKuliah</span>
            <p class="text-xs text-emerald-300 mt-0.5">Panel BAAK</p>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
            <a href="{{ route('baak.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.dashboard') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-xs font-semibold text-emerald-400 uppercase tracking-wider">Master Data</div>

            <a href="{{ route('baak.master.dosen') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.master.dosen*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Data Dosen
            </a>
            <a href="{{ route('baak.master.ruangan') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.master.ruangan*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Data Ruangan
            </a>
            <a href="{{ route('baak.master.matakuliah') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.master.matakuliah*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Mata Kuliah
            </a>
            <a href="{{ route('baak.master.kelas') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.master.kelas*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6m-8 6h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Master Kelas
            </a>

            <a href="{{ route('baak.krs.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.krs*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h8m-8 6v2m0-2h8m-8-6V9a4 4 0 014-4h4M5 7h.01M5 12h.01M5 17h.01"/></svg>
                KRS Mahasiswa
            </a>

            <div class="pt-3 pb-1 px-3 text-xs font-semibold text-emerald-400 uppercase tracking-wider">Monitoring</div>

            <a href="{{ route('baak.pengajuan-dosen.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('baak.pengajuan-dosen*') ? 'bg-emerald-600 text-white' : 'text-emerald-200 hover:bg-emerald-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m-7 7h12a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-1.828-1.828A1 1 0 0011.172 2H6a2 2 0 00-2 2v15a2 2 0 002 2z"/></svg>
                Pengajuan Dosen
            </a>

            <a href="{{ route('live.dashboard') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-emerald-200 hover:bg-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                📺 Papan Jadwal Live
                <span class="ml-auto text-xs bg-emerald-500 text-white px-1.5 py-0.5 rounded font-bold">LIVE</span>
            </a>
        </nav>

        <div class="px-3 py-3 border-t border-emerald-700 text-xs">
            <p class="text-emerald-300 truncate">{{ auth()->user()->name }}</p>
            <p class="text-emerald-400">BAAK Administrator</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="text-red-300 hover:text-red-100 transition">Keluar →</button>
            </form>
        </div>
    </aside>

    {{-- ── Main Content ─────────────────────────────────────────────── --}}
    <main class="flex-1 overflow-y-auto">
        @if(session('success'))
        <div id="flash-success" class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between">
            <span>✅ {{ session('success') }}</span>
            <button onclick="document.getElementById('flash-success').remove()" class="text-green-500 hover:text-green-700">✕</button>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
            ❌ {{ session('error') }}
        </div>
        @endif

        {{ $slot }}
    </main>
</div>

<script src="{{ mix('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
