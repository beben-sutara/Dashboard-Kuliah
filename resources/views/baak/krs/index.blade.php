<x-baak-layout title="KRS Mahasiswa">
<div class="p-5">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-800">📝 KRS Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar kelas aktif mahasiswa yang menjadi sumber tampilan portal mahasiswa.</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Tambah KRS</button>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <p class="font-semibold mb-1">Data KRS belum dapat disimpan.</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Mahasiswa</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jadwal</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Semester Akademik</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($krsMahasiswa as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->mahasiswa?->nama }}</p>
                        <p class="text-xs text-gray-500">NIM {{ $item->mahasiswa?->nim }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->jadwal?->matakuliah?->nama }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $item->jadwal?->hari }}
                            · {{ $item->jadwal?->waktu_mulai ? \Carbon\Carbon::parse($item->jadwal?->waktu_mulai)->format('H:i') : '—' }}
                            @if($item->jadwal?->waktu_selesai)
                            – {{ \Carbon\Carbon::parse($item->jadwal?->waktu_selesai)->format('H:i') }}
                            @endif
                            · {{ $item->jadwal?->ruangan?->kode }}
                        </p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->semester_akademik }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ match($item->status) {
                                'aktif' => 'bg-emerald-100 text-emerald-700',
                                'selesai' => 'bg-slate-100 text-slate-700',
                                default => 'bg-amber-100 text-amber-700',
                            } }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <button type="button"
                                onclick='openEditKrs({{ $item->id }}, @json([
                                    "mahasiswa_id" => $item->mahasiswa_id,
                                    "jadwal_id" => $item->jadwal_id,
                                    "semester_akademik" => $item->semester_akademik,
                                    "status" => $item->status,
                                ]))'
                                class="text-xs px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                            <form method="POST" action="{{ route('baak.krs.destroy', $item->id) }}" onsubmit="return confirm('Hapus data KRS ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data KRS mahasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $krsMahasiswa->links() }}
    </div>
</div>

<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-[30rem] p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Tambah KRS Mahasiswa</h2>
        <form method="POST" action="{{ route('baak.krs.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            @include('baak.krs.partials.form-fields', ['prefix' => 'add'])
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-[30rem] p-6">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Edit KRS Mahasiswa</h2>
        <form id="edit-form" method="POST" action="" class="space-y-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="krs_id" id="edit-krs-id" value="{{ old('krs_id') }}">
            @include('baak.krs.partials.form-fields', ['prefix' => 'edit'])
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
const addModal = document.getElementById('modal-add');
const editModal = document.getElementById('modal-edit');
const editForm = document.getElementById('edit-form');

function openEditKrs(id, data) {
    editForm.action = `/baak/krs/${id}`;
    document.getElementById('edit-krs-id').value = id;
    document.getElementById('edit-mahasiswa-id').value = data.mahasiswa_id;
    document.getElementById('edit-jadwal-id').value = data.jadwal_id;
    document.getElementById('edit-semester-akademik').value = data.semester_akademik;
    document.getElementById('edit-status').value = data.status;
    editModal.classList.remove('hidden');
}

@if($errors->any())
window.addEventListener('DOMContentLoaded', () => {
    const formMode = @json(old('form_mode', 'create'));
    const krsId = @json(old('krs_id'));

    if (formMode === 'edit' && krsId) {
        editForm.action = `/baak/krs/${krsId}`;
        editModal.classList.remove('hidden');
        return;
    }

    addModal.classList.remove('hidden');
});
@endif
</script>
@endpush
</x-baak-layout>
