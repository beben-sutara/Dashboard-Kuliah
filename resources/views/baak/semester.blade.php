<x-baak-layout title="Semester Akademik">

<div class="px-6 py-5 border-b border-gray-200 bg-white flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">📅 Manajemen Semester Akademik</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola tahun akademik dan semester aktif</p>
    </div>
    @php $aktif = $semesters->firstWhere('is_aktif', true); @endphp
    @if($aktif)
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2 text-sm">
            <span class="text-gray-500">Semester Aktif:</span>
            <span class="font-bold text-emerald-700">{{ $aktif->nama }}</span>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2 text-sm text-yellow-700">
            ⚠️ Belum ada semester aktif
        </div>
    @endif
</div>

<div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Form Tambah Semester ──────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 h-fit">
        <h3 class="font-semibold text-gray-700 mb-4">➕ Tambah Semester Baru</h3>

        @if($errors->any())
            <div class="mb-3 bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2 rounded-lg">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('baak.semester.store') }}" class="space-y-3">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Semester</label>
                <select name="tipe" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-transparent">
                    <option value="Ganjil" {{ old('tipe') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="Genap" {{ old('tipe') == 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Mulai</label>
                    <input type="number" name="tahun_mulai" min="2020" max="2040"
                        value="{{ old('tahun_mulai', date('Y')) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Akhir</label>
                    <input type="number" name="tahun_akhir" min="2020" max="2040"
                        value="{{ old('tahun_akhir', date('Y') + 1) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-transparent">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2 rounded-lg transition">
                💾 Simpan Semester
            </button>
        </form>
    </div>

    {{-- ── Daftar Semester ───────────────────────────── --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">📋 Daftar Semester Akademik</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-gray-500 text-xs uppercase">
                        <th class="py-2 px-3">Nama</th>
                        <th class="py-2 px-3 text-center">Tipe</th>
                        <th class="py-2 px-3 text-center">Periode</th>
                        <th class="py-2 px-3 text-center">Jadwal</th>
                        <th class="py-2 px-3 text-center">Status</th>
                        <th class="py-2 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesters as $s)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $s->is_aktif ? 'bg-emerald-50/50' : '' }}">
                        <td class="py-3 px-3 font-semibold text-gray-700">
                            {{ $s->nama }}
                            @if($s->is_aktif)
                                <span class="ml-1 text-xs bg-emerald-500 text-white px-1.5 py-0.5 rounded-full font-bold">AKTIF</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $s->tipe == 'Ganjil' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $s->tipe }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center text-xs text-gray-500">
                            @if($s->tanggal_mulai && $s->tanggal_selesai)
                                {{ $s->tanggal_mulai->format('d M Y') }} — {{ $s->tanggal_selesai->format('d M Y') }}
                            @else
                                <span class="text-gray-300">Belum diset</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $s->jadwalPerkuliahan()->count() }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if(!$s->is_aktif)
                                <form method="POST" action="{{ route('baak.semester.set-aktif', $s) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        onclick="return confirm('Set {{ $s->nama }} sebagai semester aktif?')"
                                        class="text-xs bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-2 py-1 rounded-lg font-semibold transition">
                                        Set Aktif
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-emerald-600 font-semibold">✅ Aktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Edit Periode --}}
                                <button onclick="toggleEdit({{ $s->id }})"
                                    class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-2 py-1 rounded-lg transition">
                                    ✏️
                                </button>

                                {{-- Delete --}}
                                @if(!$s->is_aktif)
                                <form method="POST" action="{{ route('baak.semester.destroy', $s) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Hapus semester {{ $s->nama }}?')"
                                        class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-2 py-1 rounded-lg transition">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                            </div>

                            {{-- Inline Edit Form --}}
                            <form id="edit-{{ $s->id }}" method="POST"
                                action="{{ route('baak.semester.update', $s) }}"
                                class="hidden mt-2 text-left bg-gray-50 rounded-lg p-2 space-y-1">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-2 gap-1">
                                    <input type="date" name="tanggal_mulai"
                                        value="{{ $s->tanggal_mulai?->format('Y-m-d') }}"
                                        class="text-xs border border-gray-200 rounded px-2 py-1">
                                    <input type="date" name="tanggal_selesai"
                                        value="{{ $s->tanggal_selesai?->format('Y-m-d') }}"
                                        class="text-xs border border-gray-200 rounded px-2 py-1">
                                </div>
                                <button type="submit" class="text-xs bg-emerald-600 text-white px-3 py-1 rounded font-semibold">
                                    Simpan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada semester akademik. Tambahkan di form sebelah kiri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.classList.toggle('hidden');
}
</script>
@endpush

</x-baak-layout>
