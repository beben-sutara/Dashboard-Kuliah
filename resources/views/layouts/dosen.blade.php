<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Dosen — JadwalKuliah</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="min-h-screen bg-emerald-50 font-sans antialiased">

    <header class="bg-white border-b border-emerald-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <span class="font-bold text-emerald-700 text-lg">🎓 JadwalKuliah</span>
                <span class="ml-2 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Portal Dosen</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="text-sm text-red-500 hover:text-red-700">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
            ❌ {{ session('error') }}
        </div>
        @endif

        {{ $slot }}
    </main>

    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
