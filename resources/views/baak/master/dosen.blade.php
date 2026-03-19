<x-baak-layout title="Master Data Dosen">
<div class="p-5">
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-gray-800">👨‍🏫 Master Data Dosen</h1>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tambah Dosen</button>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✅ {{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">NUPTK</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Program Studi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jml Jadwal</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dosen as $d)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $d->nidn ?: '-' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $d->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $d->prodi }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-emerald-100 text-emerald-700 text-xs rounded-full font-medium">{{ $d->jadwalPerkuliahan->count() }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <button onclick='openEditDosen({{ $d->id }}, {{ json_encode(["nidn"=>$d->nidn,"nama"=>$d->nama,"prodi"=>$d->prodi]) }})'
                                class="text-xs px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                            <form method="POST" action="{{ route('baak.master.dosen.destroy', $d->id) }}" onsubmit="return confirm('Hapus dosen ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">Belum ada data dosen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $dosen->links() }}
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah Dosen</h2>
        <form method="POST" action="{{ route('baak.master.dosen.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">NUPTK <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="text" name="nidn" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Kosongkan jika belum ada">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Program Studi</label>
                <input type="text" name="prodi" list="prodi-dosen"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                <datalist id="prodi-dosen">
                    <option>Teknik Informatika</option><option>Sistem Informasi</option>
                    <option>Matematika</option><option>Manajemen</option>
                </datalist>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Edit Dosen</h2>
        <form id="edit-form" method="POST" action="" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">NUPTK <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="text" name="nidn" id="edit-nidn" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Kosongkan jika belum ada">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" id="edit-nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Program Studi</label>
                <input type="text" name="prodi" id="edit-prodi" list="prodi-dosen"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-amber-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-amber-600">Update</button>
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditDosen(id, data) {
    document.getElementById('edit-form').action = `/baak/master/dosen/${id}`;
    document.getElementById('edit-nidn').value = data.nidn ?? '';
    document.getElementById('edit-nama').value = data.nama;
    document.getElementById('edit-prodi').value = data.prodi;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush
</x-baak-layout>
