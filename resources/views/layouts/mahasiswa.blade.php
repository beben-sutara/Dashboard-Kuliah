<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Mahasiswa — JadwalKuliah</title>
    <style>body { font-family: 'Segoe UI', system-ui, sans-serif; }</style>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="min-h-screen bg-emerald-50 font-sans antialiased pb-20">

    {{-- Top Bar --}}
    <header class="bg-white/90 backdrop-blur-sm sticky top-0 z-30 border-b border-emerald-200 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <span class="font-bold text-emerald-700 text-lg">🎓 JadwalKuliah</span>
                <span class="ml-2 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Mahasiswa</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-sm text-red-500 hover:text-red-700 transition">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Notifications --}}
    @if(session('success'))
    <div class="max-w-2xl mx-auto mt-3 px-4">
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
            ✅ {{ session('success') }}
        </div>
    </div>
    @endif

    <main class="max-w-2xl mx-auto px-4 py-5">
        {{ $slot }}
    </main>

    {{-- INSUN AI Floating Widget --}}
    <x-insun-chat />

    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
