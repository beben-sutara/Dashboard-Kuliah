<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JadwalKuliah') }} - {{ $title ?? 'Dashboard' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-indigo-800 text-white flex-shrink-0 flex flex-col">
                <div class="flex items-center justify-center h-16 bg-indigo-900">
                    <span class="text-xl font-bold tracking-wide">📅 JadwalKuliah</span>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('dashboard') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">🏠</span> Dashboard
                    </a>
                    <a href="{{ route('jadwal.index') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('jadwal.*') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">📅</span> Jadwal Kuliah
                    </a>
                    <a href="{{ route('mata-kuliah.index') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('mata-kuliah.*') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">📚</span> Mata Kuliah
                    </a>
                    <a href="{{ route('dosen.index') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('dosen.*') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">👨‍🏫</span> Dosen
                    </a>
                    <a href="{{ route('mahasiswa.index') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('mahasiswa.*') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">👨‍🎓</span> Mahasiswa
                    </a>
                    <a href="{{ route('ruang.index') }}"
                       class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition
                              {{ request()->routeIs('ruang.*') ? 'bg-indigo-900 text-white' : 'text-indigo-100 hover:bg-indigo-700' }}">
                        <span class="mr-3">🏫</span> Ruang Kelas
                    </a>
                </nav>
                <div class="px-4 py-4 border-t border-indigo-700">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-3 py-2 rounded-md text-sm text-indigo-100 hover:bg-indigo-700 transition">
                            <span class="mr-3">🚪</span> Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Bar -->
                <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
                    <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-600 hover:underline">Profil</a>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
