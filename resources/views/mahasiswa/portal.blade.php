<x-mahasiswa-layout>
<div class="mb-4">
    <h1 class="text-xl font-bold text-gray-800">📚 Portal Mahasiswa</h1>
    <p class="text-sm text-gray-500 mt-1">
        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }} •
        <span id="live-clock" class="font-mono">{{ now()->format('H:i:s') }}</span>
    </p>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
    <span>✅</span> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-800 flex items-center gap-2">
    <span>⚠️</span> {{ session('error') }}
</div>
@endif

{{-- Profile Banner --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 mb-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">{{ $mahasiswa->nama }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                @if($profilBelumLengkap)
                    <span class="text-yellow-600 font-medium">NIM belum diisi</span>
                @else
                    NIM <span class="font-mono">{{ $mahasiswa->nim }}</span>
                @endif
                &nbsp;·&nbsp; {{ $mahasiswa->program_studi }}
                &nbsp;·&nbsp; Angkatan {{ $mahasiswa->angkatan }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            @forelse($semesterAktif as $semester)
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                Semester Aktif: {{ $semester }}
            </span>
            @empty
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                Belum ada KRS aktif
            </span>
            @endforelse
            <button onclick="toggleProfilForm()" class="text-xs px-3 py-1.5 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50">
                ✏️ Edit Profil
            </button>
        </div>
    </div>

    {{-- Inline profile completion form --}}
    <div id="profil-form-wrap" class="{{ $profilBelumLengkap ? '' : 'hidden' }} mt-4 border-t border-gray-100 pt-4">
        @if($profilBelumLengkap)
        <p class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 mb-3">
            ⚠️ Lengkapi data profil Anda agar KRS dan laporan tercatat dengan benar.
        </p>
        @endif
        <form action="{{ route('mahasiswa.profil.update') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf @method('PATCH')
            <div>
                <label class="text-xs font-medium text-gray-600">NIM</label>
                <input type="text" name="nim" value="{{ $profilBelumLengkap ? '' : $mahasiswa->nim }}"
                    placeholder="Masukkan NIM"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                @error('nim') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Program Studi</label>
                <input type="text" name="program_studi" value="{{ $mahasiswa->program_studi === 'Belum Diisi' ? '' : $mahasiswa->program_studi }}"
                    placeholder="Contoh: Teknik Informatika"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                @error('program_studi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Angkatan</label>
                <input type="number" name="angkatan" value="{{ $mahasiswa->angkatan }}"
                    min="2000" max="{{ date('Y') + 1 }}"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                @error('angkatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium">
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>

@if($hasKrs)
{{-- ── Has KRS: show enrolled schedule ─────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">My Active Courses</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $mataKuliahAktif->count() }}</p>
        <p class="mt-1 text-sm text-gray-500">Mata kuliah yang Anda ambil di KRS aktif.</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">My Daily Schedule</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $jadwalHariIni->count() }}</p>
        <p class="mt-1 text-sm text-gray-500">Kelas terjadwal khusus hari {{ $hariIni }}.</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Weekly Registered Classes</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $jadwal->count() }}</p>
        <p class="mt-1 text-sm text-gray-500">Semua jadwal yang terhubung dengan KRS aktif Anda.</p>
    </div>
</div>

<section class="mb-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="font-semibold text-gray-900">My Active Courses</h2>
            <p class="text-sm text-gray-500">Daftar mata kuliah yang resmi Anda ambil semester ini.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @forelse($mataKuliahAktif as $mataKuliah)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <h3 class="font-semibold text-gray-900">{{ $mataKuliah->matakuliah?->nama }}</h3>
            <p class="text-sm text-gray-600 mt-1">👨‍🏫 {{ $mataKuliah->dosen?->nama }}</p>
            <p class="text-xs text-gray-500 mt-2">
                {{ $mataKuliah->hari }}
                · {{ \Carbon\Carbon::parse($mataKuliah->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($mataKuliah->waktu_selesai)->format('H:i') }}
                · {{ $mataKuliah->ruangan?->kode }}
            </p>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-6 text-center text-gray-400 sm:col-span-2">
            Belum ada mata kuliah aktif di KRS Anda.
        </div>
        @endforelse
    </div>
</section>

<section class="mb-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="font-semibold text-gray-900">My Daily Schedule</h2>
            <p class="text-sm text-gray-500">Card schedule hanya untuk kelas yang masuk KRS aktif Anda.</p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
            Default: {{ $hariIni }}
        </span>
    </div>
</section>

<div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
    <button onclick="filterHari('all')" id="filter-all"
        class="hari-btn whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:border-emerald-400 hover:text-emerald-600 transition">
        Semua
    </button>
    @foreach(\App\Models\JadwalPerkuliahan::HARI_ORDER as $hari)
    <button onclick="filterHari('{{ $hari }}')" id="filter-{{ $hari }}"
        class="hari-btn whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:border-emerald-400 hover:text-emerald-600 transition">
        {{ $hari }}
    </button>
    @endforeach
</div>

<div id="jadwal-cards" class="space-y-3">
    @forelse($jadwal as $j)
    @php $aktif = $j->isAktif(); @endphp
    <div class="jadwal-card bg-white rounded-2xl shadow-sm border overflow-hidden transition hover:shadow-md {{ $aktif ? 'border-green-400 ring-2 ring-green-200' : 'border-gray-100' }}"
         data-hari="{{ $j->hari }}">

        @if($aktif)
        <div class="bg-green-500 text-white text-xs px-4 py-1 flex items-center gap-1.5 font-medium">
            <span class="w-2 h-2 bg-white rounded-full animate-pulse inline-block"></span>
            KELAS SEDANG BERLANGSUNG
        </div>
        @endif

        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-base leading-tight">{{ $j->matakuliah?->nama }}</h3>
                    <p class="text-sm text-gray-600 mt-0.5">👨‍🏫 {{ $j->dosen?->nama }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ match($j->hari) {
                            'Senin'  =>'bg-blue-100 text-blue-700',
                            'Selasa' =>'bg-purple-100 text-purple-700',
                            'Rabu'   =>'bg-green-100 text-green-700',
                            'Kamis'  =>'bg-yellow-100 text-yellow-700',
                            'Jumat'  =>'bg-orange-100 text-orange-700',
                            default  =>'bg-gray-100 text-gray-700',
                        } }}">{{ $j->hari }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-3 text-xs text-gray-600">
                <div class="flex items-center gap-1">
                    <span>🕐</span>
                    <span class="font-mono font-medium">
                        {{ \Carbon\Carbon::parse($j->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($j->waktu_selesai)->format('H:i') }}
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <span>🏫</span>
                    <span>{{ $j->ruangan?->kode }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span>🎓</span>
                    <span class="truncate">{{ $j->prodi }}</span>
                </div>
            </div>

            <div class="mt-2 text-xs text-gray-400">Semester Akademik Aktif: {{ $semesterAktif->implode(', ') ?: '—' }}</div>

            <div class="mt-3 flex justify-between items-center">
                <button onclick="unenrollKrs(this)"
                    class="text-xs px-3 py-1.5 rounded-full border border-gray-300 text-gray-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition"
                    data-jadwal-id="{{ $j->id }}">
                    ✕ Hapus dari KRS
                </button>
                <button onclick="openReportModal(this)"
                    class="text-xs px-3 py-1.5 rounded-full border border-orange-300 text-orange-600 hover:bg-orange-50 active:bg-orange-100 transition flex items-center gap-1"
                    data-jadwal-id="{{ $j->id }}"
                    data-label="{{ $j->matakuliah?->nama ?? 'kelas ini' }}"
                    data-default-text="🚨 Laporkan Kelas">
                    🚨 Laporkan Kelas
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-gray-400 bg-white rounded-2xl border border-dashed border-gray-300">
        <div class="text-4xl mb-3">📭</div>
        <p>Belum ada jadwal yang terhubung ke KRS aktif Anda.</p>
    </div>
    @endforelse
</div>

{{-- Report Modal --}}
<div id="report-modal" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Laporkan Kondisi Kelas</h3>
                <p class="text-sm text-gray-500 mt-1">Sampaikan kondisi perkuliahan untuk <span id="report-modal-label" class="font-medium text-gray-700">kelas ini</span>.</p>
            </div>
            <button type="button" onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form id="report-form" class="mt-5 space-y-4">
            <input type="hidden" id="report-jadwal-id">

            <div>
                <label for="report-jenis" class="text-sm font-medium text-gray-700">Jenis Laporan</label>
                <select id="report-jenis"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <option value="dosen_tidak_hadir">Dosen tidak hadir</option>
                    <option value="hanya_memberi_tugas">Dosen hanya memberi tugas</option>
                </select>
            </div>

            <div>
                <label for="report-catatan" class="text-sm font-medium text-gray-700">Catatan Mahasiswa <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea id="report-catatan" rows="4"
                    class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                    placeholder="Tambahkan detail singkat jika diperlukan..."></textarea>
            </div>

            <div id="report-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

            <div class="flex gap-2 pt-2">
                <button type="submit" id="report-submit"
                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg text-sm font-medium transition">
                    Kirim Laporan
                </button>
                <button type="button" onclick="closeReportModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@else
{{-- ── No KRS: show all available jadwal for self-enrollment ──────── --}}
<div class="mb-5 bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start gap-3">
    <span class="text-2xl">📋</span>
    <div>
        <p class="font-semibold text-indigo-800">Pilih Jadwal Perkuliahan Anda</p>
        <p class="text-sm text-indigo-700 mt-1">
            Klik <strong>Ambil</strong> pada jadwal yang ingin Anda ikuti semester ini.
            Jadwal yang Anda pilih akan masuk ke KRS dan muncul di halaman ini.
        </p>
    </div>
</div>

@if($jadwalTersedia->isEmpty())
<div class="text-center py-16 text-gray-400 bg-white rounded-2xl border border-dashed border-gray-300">
    <div class="text-4xl mb-3">📭</div>
    <p>Belum ada jadwal tersedia. BAAK akan segera menambahkan jadwal perkuliahan.</p>
</div>
@else
<div id="toast-enroll" class="hidden fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-xl shadow-lg transition-opacity"></div>

<div class="space-y-3" id="available-jadwal">
    @foreach($jadwalTersedia as $j)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition"
         id="avail-{{ $j->id }}">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-base leading-tight">{{ $j->matakuliah?->nama }}</h3>
                    <p class="text-sm text-gray-600 mt-0.5">👨‍🏫 {{ $j->dosen?->nama }}</p>
                    @if($j->kelas)
                    <p class="text-xs text-indigo-600 mt-0.5 font-medium">{{ $j->kelas->nama }} · Smt {{ $j->kelas->semester }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ match($j->hari) {
                            'Senin'  =>'bg-blue-100 text-blue-700',
                            'Selasa' =>'bg-purple-100 text-purple-700',
                            'Rabu'   =>'bg-green-100 text-green-700',
                            'Kamis'  =>'bg-yellow-100 text-yellow-700',
                            'Jumat'  =>'bg-orange-100 text-orange-700',
                            default  =>'bg-gray-100 text-gray-700',
                        } }}">{{ $j->hari }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-3 text-xs text-gray-600">
                <div class="flex items-center gap-1">
                    <span>🕐</span>
                    <span class="font-mono font-medium">
                        {{ \Carbon\Carbon::parse($j->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($j->waktu_selesai)->format('H:i') }}
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <span>🏫</span>
                    <span>{{ $j->ruangan?->kode }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span>🎓</span>
                    <span class="truncate">{{ $j->prodi }}</span>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button onclick="enrollKrs(this)"
                    class="enroll-btn text-xs px-4 py-1.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition"
                    data-jadwal-id="{{ $j->id }}">
                    + Ambil
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endif

@push('scripts')
<script>
setInterval(() => {
    const now = new Date();
    document.getElementById('live-clock').textContent =
        String(now.getHours()).padStart(2,'0') + ':' +
        String(now.getMinutes()).padStart(2,'0') + ':' +
        String(now.getSeconds()).padStart(2,'0');
}, 1000);

function toggleProfilForm() {
    const wrap = document.getElementById('profil-form-wrap');
    wrap.classList.toggle('hidden');
}

// ── Enrollment (no-KRS state) ─────────────────────────────────────────────────
function showToast(msg, success = true) {
    const t = document.getElementById('toast-enroll');
    if (!t) return;
    t.textContent = msg;
    t.classList.remove('hidden', 'opacity-0');
    t.style.background = success ? '#166534' : '#991b1b';
    setTimeout(() => t.classList.add('hidden'), 3000);
}

async function enrollKrs(btn) {
    btn.disabled = true;
    btn.textContent = '⏳...';
    const jadwalId = btn.dataset.jadwalId;
    try {
        const res = await fetch('{{ route("mahasiswa.krs.enroll") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ jadwal_id: jadwalId }),
        });
        const data = await res.json();
        if (data.success) {
            showToast('✅ ' + data.message);
            btn.textContent = '✅ Diambil';
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btn.classList.add('bg-green-600', 'cursor-default');
            // Reload after short delay so portal refreshes to KRS view
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('⚠️ ' + (data.message || 'Gagal menambahkan.'), false);
            btn.disabled = false;
            btn.textContent = '+ Ambil';
        }
    } catch {
        showToast('⚠️ Terjadi gangguan jaringan.', false);
        btn.disabled = false;
        btn.textContent = '+ Ambil';
    }
}

// ── Unenroll (has-KRS state) ──────────────────────────────────────────────────
async function unenrollKrs(btn) {
    if (!confirm('Hapus jadwal ini dari KRS Anda?')) return;
    btn.disabled = true;
    const jadwalId = btn.dataset.jadwalId;
    try {
        const res = await fetch('{{ route("mahasiswa.krs.unenroll") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ jadwal_id: jadwalId }),
        });
        const data = await res.json();
        if (data.success) {
            btn.closest('.jadwal-card').style.transition = 'opacity 0.4s';
            btn.closest('.jadwal-card').style.opacity = '0';
            setTimeout(() => { location.reload(); }, 500);
        } else {
            alert(data.message || 'Gagal menghapus dari KRS.');
            btn.disabled = false;
        }
    } catch {
        alert('Terjadi gangguan jaringan.');
        btn.disabled = false;
    }
}

// ── Enrolled-jadwal filter & report (has-KRS state) ──────────────────────────
function activateFilterButton(hari) {
    document.querySelectorAll('.hari-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600','text-white');
        btn.classList.add('bg-white','border','border-gray-200','text-gray-700');
    });

    const activeBtn = document.getElementById(hari === 'all' ? 'filter-all' : `filter-${hari}`);
    if (!activeBtn) return;
    activeBtn.classList.add('bg-indigo-600','text-white');
    activeBtn.classList.remove('bg-white','border-gray-200','text-gray-700');
}

function filterHari(hari) {
    activateFilterButton(hari);
    document.querySelectorAll('.jadwal-card').forEach(card => {
        card.style.display = (hari === 'all' || card.dataset.hari === hari) ? '' : 'none';
    });
}

const reportModal  = document.getElementById('report-modal');
const reportForm   = document.getElementById('report-form');
const reportModalLabel = document.getElementById('report-modal-label');
const reportJadwalId   = document.getElementById('report-jadwal-id');
const reportJenis      = document.getElementById('report-jenis');
const reportCatatan    = document.getElementById('report-catatan');
const reportSubmit     = document.getElementById('report-submit');
const reportError      = document.getElementById('report-error');
let activeReportButton = null;

function openReportModal(button) {
    if (!reportModal || button.dataset.reported) return;
    activeReportButton = button;
    reportModalLabel.textContent = button.dataset.label || 'kelas ini';
    reportJadwalId.value = button.dataset.jadwalId || '';
    reportJenis.value = 'dosen_tidak_hadir';
    reportCatatan.value = '';
    reportError.classList.add('hidden');
    reportError.textContent = '';
    reportSubmit.disabled = false;
    reportSubmit.textContent = 'Kirim Laporan';
    reportModal.classList.remove('hidden');
    reportModal.classList.add('flex');
}

function closeReportModal() {
    if (!reportModal) return;
    reportModal.classList.add('hidden');
    reportModal.classList.remove('flex');
    reportError.classList.add('hidden');
    reportError.textContent = '';
}

async function submitReport(event) {
    event.preventDefault();
    if (!activeReportButton) return;

    reportSubmit.disabled = true;
    reportSubmit.textContent = '⏳ Mengirim...';
    reportError.classList.add('hidden');
    reportError.textContent = '';

    try {
        const res = await fetch('{{ route("mahasiswa.laporan") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                jadwal_id: reportJadwalId.value,
                jenis_laporan: reportJenis.value,
                catatan_mahasiswa: reportCatatan.value,
            }),
        });
        const data = await res.json();

        if (data.success) {
            activeReportButton.textContent = '✅ Laporan Terkirim';
            activeReportButton.dataset.reported = '1';
            activeReportButton.disabled = true;
            activeReportButton.classList.remove('border-orange-300','text-orange-600');
            activeReportButton.classList.add('border-green-300','text-green-600','cursor-default');
            closeReportModal();
            return;
        }

        reportError.textContent = data.message || 'Laporan gagal dikirim.';
        reportError.classList.remove('hidden');
        reportSubmit.disabled = false;
        reportSubmit.textContent = 'Kirim Laporan';
    } catch (e) {
        reportError.textContent = 'Terjadi gangguan saat mengirim laporan.';
        reportError.classList.remove('hidden');
        reportSubmit.disabled = false;
        reportSubmit.textContent = 'Kirim Laporan';
    }
}

if (reportForm) {
    reportForm.addEventListener('submit', submitReport);
}

if (window.Echo) {
    window.Echo.channel('jadwal')
        .listen('.JadwalCreated', () => location.reload())
        .listen('.JadwalUpdated', () => location.reload())
        .listen('.JadwalDeleted', (e) => {
            const card = document.querySelector(`.jadwal-card button[data-jadwal-id="${e.jadwal_id}"]`)?.closest('.jadwal-card');
            if (card) {
                card.style.transition = 'opacity 0.5s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 500);
            }
        });
}

@if($hasKrs)
filterHari(@json($hariIni));
@endif
</script>
@endpush
</x-mahasiswa-layout>
