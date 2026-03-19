<x-baak-layout title="Mata Kuliah">
<div class="p-5">
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-gray-800">📚 Master Mata Kuliah</h1>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tambah MK</button>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✅ {{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">#</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Kode</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Mata Kuliah</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">SKS</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Waktu</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($matakuliah as $i => $mk)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $matakuliah->firstItem() + $i }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $mk->kode ?? '—' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $mk->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $mk->sks ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($mk->waktu)
                            <span class="font-mono text-xs">{{ $mk->waktu }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <button onclick="openEdit(this)"
                                data-id="{{ $mk->id }}"
                                data-kode="{{ $mk->kode ?? '' }}"
                                data-nama="{{ $mk->nama }}"
                                data-sks="{{ $mk->sks ?? '' }}"
                                data-waktu="{{ $mk->waktu ?? '' }}"
                                class="text-xs px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                            <form method="POST" action="{{ route('baak.master.matakuliah.destroy', $mk->id) }}" onsubmit="return confirm('Hapus mata kuliah ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Belum ada data mata kuliah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $matakuliah->links() }}
</div>

<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4">Tambah Mata Kuliah</h2>
        <form method="POST" action="{{ route('baak.master.matakuliah.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Kode</label>
                <input type="text" name="kode" placeholder="IF101"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Mata Kuliah</label>
                <input type="text" name="nama" placeholder="Algoritma & Pemrograman"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-700">SKS</label>
                    <input type="number" name="sks" min="1" max="6" value="3"
                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Waktu</label>
                    <input type="text" name="waktu" placeholder="08:00-10:30" pattern="\d{2}:\d{2}-\d{2}:\d{2}"
                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none font-mono">
                </div>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4">Edit Mata Kuliah</h2>
        <form id="edit-form" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">Kode</label>
                <input type="text" name="kode" id="edit-kode"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Mata Kuliah</label>
                <input type="text" name="nama" id="edit-nama"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-gray-700">SKS</label>
                    <input type="number" name="sks" min="1" max="6" id="edit-sks"
                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Waktu</label>
                    <input type="text" name="waktu" id="edit-waktu" placeholder="08:00-10:30" pattern="\d{2}:\d{2}-\d{2}:\d{2}"
                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none font-mono">
                </div>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-amber-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-amber-600">Update</button>
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(btn) {
    document.getElementById('edit-form').action = `/baak/master/matakuliah/${btn.dataset.id}`;
    document.getElementById('edit-kode').value  = btn.dataset.kode  || '';
    document.getElementById('edit-nama').value  = btn.dataset.nama  || '';
    document.getElementById('edit-sks').value   = btn.dataset.sks   || 3;
    document.getElementById('edit-waktu').value = btn.dataset.waktu || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush
</x-baak-layout>
