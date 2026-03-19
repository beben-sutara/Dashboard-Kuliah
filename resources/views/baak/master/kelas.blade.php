<x-baak-layout title="Master Kelas">
<div class="p-5">
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-gray-800">🏷️ Master Kelas</h1>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tambah Kelas</button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Kelas</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Semester</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($kelas as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->semester }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <button onclick='openEditKelas({{ $item->id }}, @json(["nama" => $item->nama, "semester" => $item->semester]))'
                                class="text-xs px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                            <form method="POST" action="{{ route('baak.master.kelas.destroy', $item->id) }}" onsubmit="return confirm('Hapus kelas ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">Belum ada master kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $kelas->links() }}
</div>

<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah Kelas</h2>
        <form method="POST" action="{{ route('baak.master.kelas.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Kelas</label>
                <input type="text" name="nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Contoh: TI-3A" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Semester</label>
                <input type="text" name="semester" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Contoh: 3" required>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Edit Kelas</h2>
        <form id="edit-form" method="POST" action="" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Kelas</label>
                <input type="text" name="nama" id="edit-nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Semester</label>
                <input type="text" name="semester" id="edit-semester" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
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
function openEditKelas(id, data) {
    document.getElementById('edit-form').action = `/baak/master/kelas/${id}`;
    document.getElementById('edit-nama').value = data.nama;
    document.getElementById('edit-semester').value = data.semester;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush
</x-baak-layout>
