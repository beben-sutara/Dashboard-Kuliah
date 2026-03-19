<x-baak-layout title="Pengajuan Dosen">
<div class="p-5">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-800">📨 Pengajuan Dosen & Laporan Mahasiswa</h1>
        <p class="text-sm text-gray-500 mt-1">BAAK dapat meninjau pengajuan dari dosen sekaligus laporan mahasiswa tentang kelas yang dosennya tidak hadir atau hanya memberi tugas.</p>
    </div>

    <div class="mb-4">
        <h2 class="font-semibold text-gray-900">Pengajuan dari Dosen</h2>
        <p class="text-sm text-gray-500 mt-1">Review laporan berhalangan mengajar dan permintaan reschedule yang masuk dari portal dosen.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">⏳</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</div>
            <div class="text-xs text-gray-500">Pending</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">✅</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stats['disetujui'] }}</div>
            <div class="text-xs text-gray-500">Disetujui</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">❌</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stats['ditolak'] }}</div>
            <div class="text-xs text-gray-500">Ditolak</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Dosen / Kelas</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jenis</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Detail</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Tinjau</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pengajuan as $item)
                <tr class="align-top hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->dosen?->nama }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $item->jadwal?->matakuliah?->nama }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $item->jadwal?->hari }}
                            @if($item->jadwal?->waktu_mulai && $item->jadwal?->waktu_selesai)
                            · {{ \Carbon\Carbon::parse($item->jadwal?->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($item->jadwal?->waktu_selesai)->format('H:i') }}
                            @endif
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->jenis === 'lapor_absen' ? 'bg-orange-100 text-orange-700' : 'bg-sky-100 text-sky-700' }}">
                            {{ $item->jenis === 'lapor_absen' ? 'Report Absence' : 'Reschedule Request' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $item->tanggal_kelas?->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-xs text-gray-600">{{ $item->alasan }}</p>
                        @if($item->jenis === 'ajukan_jadwal_ulang')
                        <p class="text-xs text-gray-500 mt-2">
                            Pengganti:
                            {{ $item->tanggal_pengganti?->format('d M Y') }}
                            @if($item->waktu_mulai_pengganti && $item->waktu_selesai_pengganti)
                            · {{ \Carbon\Carbon::parse($item->waktu_mulai_pengganti)->format('H:i') }}–{{ \Carbon\Carbon::parse($item->waktu_selesai_pengganti)->format('H:i') }}
                            @endif
                            @if($item->ruanganPengganti)
                            · {{ $item->ruanganPengganti->kode }}
                            @endif
                        </p>
                        @endif
                        @if($item->catatan_baak)
                        <p class="text-xs text-gray-500 mt-2">Catatan BAAK: {{ $item->catatan_baak }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ match($item->status) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'disetujui' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-red-100 text-red-700',
                            } }}">
                            {{ ucfirst($item->status) }}
                        </span>
                        @if($item->reviewer)
                        <p class="text-xs text-gray-400 mt-2">
                            Ditinjau oleh {{ $item->reviewer->name }}
                            @if($item->reviewed_at)
                            · {{ $item->reviewed_at->format('d M Y H:i') }}
                            @endif
                        </p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('baak.pengajuan-dosen.update', $item->id) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="disetujui" {{ $item->status === 'disetujui' ? 'selected' : '' }}>Setujui</option>
                                <option value="ditolak" {{ $item->status === 'ditolak' ? 'selected' : '' }}>Tolak</option>
                            </select>
                            <textarea name="catatan_baak" rows="3"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                placeholder="Catatan review BAAK...">{{ $item->catatan_baak }}</textarea>
                            <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium py-2 rounded-lg transition">
                                Simpan Review
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">Belum ada pengajuan dosen.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pengajuan->links() }}
    </div>

    <div class="mt-10 mb-4">
        <h2 class="font-semibold text-gray-900">Laporan Mahasiswa</h2>
        <p class="text-sm text-gray-500 mt-1">Laporan ini dikirim mahasiswa untuk kelas yang dosennya tidak hadir atau hanya memberi tugas.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">🟡</div>
            <div class="text-2xl font-bold text-gray-800">{{ $laporanStats['pending'] }}</div>
            <div class="text-xs text-gray-500">Pending</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">✅</div>
            <div class="text-2xl font-bold text-gray-800">{{ $laporanStats['valid'] }}</div>
            <div class="text-xs text-gray-500">Valid</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">❌</div>
            <div class="text-2xl font-bold text-gray-800">{{ $laporanStats['ditolak'] }}</div>
            <div class="text-xs text-gray-500">Ditolak</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Mahasiswa / Kelas</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Jenis</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Tinjau</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($laporanMahasiswa as $laporan)
                <tr class="align-top hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $laporan->mahasiswa?->nama }}</p>
                        <p class="text-xs text-gray-500 mt-1">NIM {{ $laporan->mahasiswa?->nim ?? '—' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $laporan->jadwal?->matakuliah?->nama }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $laporan->jadwal?->dosen?->nama ?? 'Dosen tidak tersedia' }}
                            @if($laporan->jadwal?->hari)
                            · {{ $laporan->jadwal->hari }}
                            @endif
                            @if($laporan->jadwal?->waktu_mulai && $laporan->jadwal?->waktu_selesai)
                            · {{ \Carbon\Carbon::parse($laporan->jadwal->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($laporan->jadwal->waktu_selesai)->format('H:i') }}
                            @endif
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $laporan->jenis_laporan === 'hanya_memberi_tugas' ? 'bg-violet-100 text-violet-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $laporan->jenis_laporan === 'hanya_memberi_tugas' ? 'Hanya Memberi Tugas' : 'Dosen Tidak Hadir' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $laporan->tanggal?->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-xs text-gray-600">{{ $laporan->catatan_mahasiswa ?: 'Tidak ada catatan tambahan dari mahasiswa.' }}</p>
                        @if($laporan->catatan_baak)
                        <p class="text-xs text-gray-500 mt-2">Catatan BAAK: {{ $laporan->catatan_baak }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ match($laporan->status_validasi) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'valid' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-red-100 text-red-700',
                            } }}">
                            {{ ucfirst($laporan->status_validasi) }}
                        </span>
                        @if($laporan->reviewer)
                        <p class="text-xs text-gray-400 mt-2">
                            Ditinjau oleh {{ $laporan->reviewer->name }}
                            @if($laporan->reviewed_at)
                            · {{ $laporan->reviewed_at->format('d M Y H:i') }}
                            @endif
                        </p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('baak.pengajuan-dosen.laporan.update', $laporan->id) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <select name="status_validasi"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="valid" {{ $laporan->status_validasi === 'valid' ? 'selected' : '' }}>Tandai Valid</option>
                                <option value="ditolak" {{ $laporan->status_validasi === 'ditolak' ? 'selected' : '' }}>Tolak</option>
                            </select>
                            <textarea name="catatan_baak" rows="3"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                placeholder="Catatan review BAAK...">{{ $laporan->catatan_baak }}</textarea>
                            <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium py-2 rounded-lg transition">
                                Simpan Review
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">Belum ada laporan mahasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $laporanMahasiswa->links() }}
    </div>
</div>
</x-baak-layout>
