<x-dosen-layout>
@php
    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-700',
        'disetujui' => 'bg-emerald-100 text-emerald-700',
        'ditolak' => 'bg-red-100 text-red-700',
    ];
@endphp

@if($masterDosen)
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-6">
    <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-3xl bg-emerald-100 flex items-center justify-center text-3xl shadow-sm">👨‍🏫</div>
        <div>
            <h1 class="text-3xl font-bold text-emerald-800">Dashboard Dosen</h1>
            <p class="text-sm text-gray-500 mt-1">
                <span class="font-medium text-emerald-700">{{ $masterDosen->nama }}</span>
                &nbsp;·&nbsp; NUPTK <span class="font-mono">{{ $masterDosen->nidn ?: '-' }}</span>
                &nbsp;·&nbsp; {{ $masterDosen->prodi }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Portal ini hanya menampilkan kelas yang terhubung ke akun dosen Anda.</p>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <span class="bg-white border border-emerald-100 text-emerald-700 text-sm font-semibold px-3 py-2 rounded-full shadow-sm">
            📚 {{ $jadwal->count() }} kelas mingguan
        </span>
        <span class="bg-white border border-emerald-100 text-emerald-700 text-sm font-semibold px-3 py-2 rounded-full shadow-sm">
            📅 {{ $kelasHariIni->count() }} kelas hari ini
        </span>
        <span class="bg-white border border-emerald-100 text-emerald-700 text-sm font-semibold px-3 py-2 rounded-full shadow-sm">
            📨 {{ $permintaanPending }} pengajuan pending
        </span>
    </div>
</div>

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-4 py-3 text-sm text-red-800">
    <p class="font-semibold mb-1">Pengajuan belum dapat diproses.</p>
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">My Classes Today</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $kelasHariIni->count() }}</p>
        <p class="mt-1 text-sm text-gray-500">{{ $hariIni }} · {{ now()->format('d M Y') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Weekly Load</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $jadwal->count() }}</p>
        <p class="mt-1 text-sm text-gray-500">Semua jadwal kelas milik Anda minggu ini.</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Request Queue</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $permintaanPending }}</p>
        <p class="mt-1 text-sm text-gray-500">Laporan absen atau reschedule yang masih menunggu review BAAK.</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-900">My Classes Today</h2>
                    <p class="text-sm text-gray-500">Ringkasan kelas Anda pada {{ $hariIni }}.</p>
                </div>
                <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                    {{ $kelasHariIni->count() }} kelas
                </span>
            </div>
            <div class="p-5 space-y-4">
                @forelse($kelasHariIni as $kelas)
                @php $aktif = $kelas->isAktif(); @endphp
                <div class="rounded-2xl border {{ $aktif ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white' }} p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $kelas->matakuliah?->nama }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ \Carbon\Carbon::parse($kelas->waktu_mulai)->format('H:i') }} –
                                {{ \Carbon\Carbon::parse($kelas->waktu_selesai)->format('H:i') }}
                                &nbsp;·&nbsp; {{ $kelas->ruangan?->kode }} {{ $kelas->ruangan?->nama ? '— ' . $kelas->ruangan?->nama : '' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">{{ $kelas->prodi }} · Semester {{ $kelas->semester }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if($aktif)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Sedang berlangsung
                            </span>
                            @endif
                            <button type="button"
                                data-jadwal-id="{{ $kelas->id }}"
                                data-label="{{ $kelas->matakuliah?->nama }}"
                                onclick="openRequestModal(this, 'lapor_absen')"
                                class="text-xs px-3 py-2 rounded-full border border-orange-300 text-orange-600 hover:bg-orange-50 transition">
                                🚨 Report Absence
                            </button>
                            <button type="button"
                                data-jadwal-id="{{ $kelas->id }}"
                                data-label="{{ $kelas->matakuliah?->nama }}"
                                onclick="openRequestModal(this, 'ajukan_jadwal_ulang')"
                                class="text-xs px-3 py-2 rounded-full border border-sky-300 text-sky-600 hover:bg-sky-50 transition">
                                🔁 Reschedule Request
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">🗓️</div>
                    <p>Tidak ada kelas untuk Anda hari ini.</p>
                </div>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Personal Weekly Calendar</h2>
                <p class="text-sm text-gray-500">Kalender mingguan pribadi yang langsung difilter ke kelas milik Anda.</p>
            </div>
            <div class="overflow-x-auto">
                <div class="grid grid-cols-6 gap-4 min-w-[1024px] p-5">
                    @foreach($jadwalMingguan as $hari => $jadwalHari)
                    <div class="rounded-2xl border {{ $hari === $hariIni ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/60' }} min-h-[280px]">
                        <div class="px-4 py-3 border-b {{ $hari === $hariIni ? 'border-emerald-200' : 'border-gray-200' }}">
                            <p class="font-semibold text-gray-900">{{ $hari }}</p>
                            <p class="text-xs text-gray-500">{{ $jadwalHari->count() }} kelas</p>
                        </div>
                        <div class="p-3 space-y-3">
                            @forelse($jadwalHari as $kelas)
                            <div class="rounded-xl bg-white border border-gray-200 p-3 shadow-sm">
                                <p class="font-semibold text-sm text-gray-900">{{ $kelas->matakuliah?->nama }}</p>
                                <p class="text-xs text-gray-500 mt-1 font-mono">
                                    {{ \Carbon\Carbon::parse($kelas->waktu_mulai)->format('H:i') }} –
                                    {{ \Carbon\Carbon::parse($kelas->waktu_selesai)->format('H:i') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $kelas->ruangan?->kode }} · {{ $kelas->prodi }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button"
                                        data-jadwal-id="{{ $kelas->id }}"
                                        data-label="{{ $kelas->matakuliah?->nama }}"
                                        onclick="openRequestModal(this, 'lapor_absen')"
                                        class="text-[11px] px-2.5 py-1 rounded-full border border-orange-300 text-orange-600 hover:bg-orange-50 transition">
                                        Absen
                                    </button>
                                    <button type="button"
                                        data-jadwal-id="{{ $kelas->id }}"
                                        data-label="{{ $kelas->matakuliah?->nama }}"
                                        onclick="openRequestModal(this, 'ajukan_jadwal_ulang')"
                                        class="text-[11px] px-2.5 py-1 rounded-full border border-sky-300 text-sky-600 hover:bg-sky-50 transition">
                                        Reschedule
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="text-xs text-gray-400 italic px-1 py-2">Tidak ada kelas.</div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Recent Requests</h2>
                <p class="text-sm text-gray-500">Riwayat lima pengajuan terbaru Anda ke BAAK.</p>
            </div>
            <div class="p-5 space-y-3">
                @forelse($pengajuanTerbaru as $pengajuan)
                <div class="rounded-2xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">{{ $pengajuan->jadwal?->matakuliah?->nama }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $pengajuan->jenis === 'lapor_absen' ? 'Report Absence' : 'Reschedule Request' }}
                                · {{ $pengajuan->tanggal_kelas?->format('d M Y') }}
                            </p>
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$pengajuan->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($pengajuan->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 mt-3">{{ $pengajuan->alasan }}</p>
                    @if($pengajuan->jenis === 'ajukan_jadwal_ulang')
                    <p class="text-xs text-gray-500 mt-2">
                        Pengganti:
                        {{ $pengajuan->tanggal_pengganti?->format('d M Y') }}
                        @if($pengajuan->waktu_mulai_pengganti && $pengajuan->waktu_selesai_pengganti)
                        · {{ \Carbon\Carbon::parse($pengajuan->waktu_mulai_pengganti)->format('H:i') }} – {{ \Carbon\Carbon::parse($pengajuan->waktu_selesai_pengganti)->format('H:i') }}
                        @endif
                        @if($pengajuan->ruanganPengganti)
                        · {{ $pengajuan->ruanganPengganti->kode }}
                        @endif
                    </p>
                    @endif
                    @if($pengajuan->catatan_baak)
                    <div class="mt-2 text-xs text-gray-500 bg-gray-50 rounded-xl px-3 py-2">
                        Catatan BAAK: {{ $pengajuan->catatan_baak }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <div class="text-3xl mb-2">📭</div>
                    <p class="text-sm">Belum ada pengajuan dosen.</p>
                </div>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Cara kerja akses</h2>
            </div>
            <div class="p-5 text-sm text-gray-600 space-y-3">
                <p>Portal ini otomatis membaca akun dosen yang sedang login dan hanya mengambil jadwal dengan relasi `dosen_id` milik Anda.</p>
                <p>Setiap tombol tindakan di dashboard terikat ke kelas yang dipilih, jadi pengajuan tidak bisa dibuat untuk jadwal dosen lain.</p>
            </div>
        </section>
    </aside>
</div>

<div id="request-modal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 id="request-modal-title" class="text-lg font-bold text-gray-900">Pengajuan Dosen</h2>
                <p id="request-modal-subtitle" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <button type="button" onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600 text-xl">×</button>
        </div>
        <form method="POST" action="{{ route('dosen.pengajuan.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="jadwal_id" id="request-jadwal-id" value="{{ old('jadwal_id') }}">
            <input type="hidden" name="jenis" id="request-jenis" value="{{ old('jenis') }}">

            <div>
                <label class="text-sm font-medium text-gray-700">Tanggal kelas</label>
                <input type="date" name="tanggal_kelas" value="{{ old('tanggal_kelas', now()->toDateString()) }}"
                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Alasan</label>
                <textarea name="alasan" rows="4"
                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                    placeholder="Jelaskan alasan permintaan Anda." required>{{ old('alasan') }}</textarea>
            </div>

            <div id="reschedule-fields" class="hidden space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tanggal pengganti</label>
                        <input type="date" name="tanggal_pengganti" value="{{ old('tanggal_pengganti') }}"
                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Ruangan pengganti</label>
                        <select name="ruangan_id_pengganti"
                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            <option value="">— pilih ruangan —</option>
                            @foreach($ruangan as $room)
                            <option value="{{ $room->id }}" {{ old('ruangan_id_pengganti') == $room->id ? 'selected' : '' }}>
                                {{ $room->kode }} — {{ $room->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Mulai pengganti</label>
                        <input type="time" name="waktu_mulai_pengganti" value="{{ old('waktu_mulai_pengganti') }}"
                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Selesai pengganti</label>
                        <input type="time" name="waktu_selesai_pengganti" value="{{ old('waktu_selesai_pengganti') }}"
                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2.5 rounded-xl transition">
                    Kirim Pengajuan
                </button>
                <button type="button" onclick="closeRequestModal()" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-sm text-gray-700 rounded-xl transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@else
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-2xl p-5 flex items-start gap-3">
    <span class="text-2xl">⚠️</span>
    <div>
        <p class="font-semibold text-yellow-800">Akun belum terhubung ke data dosen</p>
        <p class="text-sm text-yellow-700 mt-1">Hubungi BAAK untuk menautkan akun Anda ke data dosen master agar dashboard personal dapat menampilkan kelas Anda.</p>
    </div>
</div>
@endif

@push('scripts')
<script>
const requestModal = document.getElementById('request-modal');
const requestTitle = document.getElementById('request-modal-title');
const requestSubtitle = document.getElementById('request-modal-subtitle');
const requestJenis = document.getElementById('request-jenis');
const requestJadwalId = document.getElementById('request-jadwal-id');
const rescheduleFields = document.getElementById('reschedule-fields');

function toggleRescheduleFields(jenis) {
    if (!rescheduleFields) {
        return;
    }

    const visible = jenis === 'ajukan_jadwal_ulang';
    rescheduleFields.classList.toggle('hidden', !visible);
    rescheduleFields.querySelectorAll('input, select').forEach((field) => {
        field.required = visible;
    });
}

function openRequestModal(button, jenis) {
    if (!requestModal) {
        return;
    }

    const label = button.dataset.label || 'kelas ini';
    requestJenis.value = jenis;
    requestJadwalId.value = button.dataset.jadwalId;
    requestTitle.textContent = jenis === 'lapor_absen' ? 'Report Absence' : 'Reschedule Request';
    requestSubtitle.textContent = `Kelas: ${label}`;
    toggleRescheduleFields(jenis);
    requestModal.classList.remove('hidden');
}

function closeRequestModal() {
    if (requestModal) {
        requestModal.classList.add('hidden');
    }
}

if (window.Echo) {
    window.Echo.channel('jadwal')
        .listen('.JadwalCreated', () => location.reload())
        .listen('.JadwalUpdated', () => location.reload())
        .listen('.JadwalDeleted', () => location.reload());
}

@if(old('jenis') || $errors->any())
if (requestModal) {
    requestModal.classList.remove('hidden');
    toggleRescheduleFields(@json(old('jenis', 'lapor_absen')));
    requestTitle.textContent = @json(old('jenis') === 'ajukan_jadwal_ulang' ? 'Reschedule Request' : 'Report Absence');
    requestSubtitle.textContent = 'Lengkapi kembali data pengajuan Anda.';
}
@endif
</script>
@endpush
</x-dosen-layout>
