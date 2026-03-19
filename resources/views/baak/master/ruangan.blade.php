<x-baak-layout title="Master Ruangan">
<div class="p-5">
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-gray-800">🏫 Master Data Ruangan</h1>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tambah Ruangan</button>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">✅ {{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Kode</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Kapasitas</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jenis</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($ruangan as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold text-emerald-600">{{ $r->kode }}</td>
                    <td class="px-4 py-3 text-gray-800">{{ $r->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->kapasitas }} orang</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($r->jenis) {
                                'Lab'    => 'bg-blue-100 text-blue-700',
                                'Aula'   => 'bg-green-100 text-green-700',
                                'Seminar'=> 'bg-purple-100 text-purple-700',
                                default  => 'bg-gray-100 text-gray-700',
                            } }}">{{ $r->jenis }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <button onclick='openEditRuangan({{ $r->id }}, {{ json_encode(["kode"=>$r->kode,"nama"=>$r->nama,"kapasitas"=>$r->kapasitas,"jenis"=>$r->jenis]) }})'
                                class="text-xs px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                            <form method="POST" action="{{ route('baak.master.ruangan.destroy', $r->id) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">Belum ada data ruangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ruangan->links() }}
</div>

<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah Ruangan</h2>
        <form method="POST" action="{{ route('baak.master.ruangan.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-sm font-medium text-gray-700">Kode</label>
                    <input type="text" name="kode" placeholder="A101" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Kapasitas</label>
                    <input type="number" name="kapasitas" min="1" placeholder="40" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Ruangan</label>
                <input type="text" name="nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Jenis</label>
                <select name="jenis" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                    @foreach(['Teori','Lab','Aula','Seminar'] as $j)
                    <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
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
        <h2 class="text-lg font-bold mb-4">Edit Ruangan</h2>
        <form id="edit-form" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-sm font-medium text-gray-700">Kode</label>
                    <input type="text" name="kode" id="edit-kode" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Kapasitas</label>
                    <input type="number" name="kapasitas" id="edit-kapasitas" min="1" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama" id="edit-nama" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Jenis</label>
                <select name="jenis" id="edit-jenis" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                    @foreach(['Teori','Lab','Aula','Seminar'] as $j)
                    <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
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
function openEditRuangan(id, data) {
    document.getElementById('edit-form').action = `/baak/master/ruangan/${id}`;
    document.getElementById('edit-kode').value = data.kode;
    document.getElementById('edit-nama').value = data.nama;
    document.getElementById('edit-kapasitas').value = data.kapasitas;
    document.getElementById('edit-jenis').value = data.jenis;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush
</x-baak-layout>
