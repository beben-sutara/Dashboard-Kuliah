<x-baak-layout title="Dashboard">
<div class="p-5">

    {{-- Semester Aktif Banner --}}
    @if($semesterAktif)
    <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-emerald-600 font-bold">📅 Semester Aktif:</span>
            <span class="text-emerald-800 font-semibold">{{ $semesterAktif->nama }}</span>
            @if($semesterAktif->tanggal_mulai && $semesterAktif->tanggal_selesai)
                <span class="text-emerald-500 text-xs">({{ $semesterAktif->tanggal_mulai->format('d M Y') }} — {{ $semesterAktif->tanggal_selesai->format('d M Y') }})</span>
            @endif
        </div>
        <a href="{{ route('baak.semester.index') }}" class="text-xs text-emerald-600 hover:text-emerald-800 underline">Kelola</a>
    </div>
    @else
    <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 flex items-center justify-between">
        <span class="text-sm text-yellow-700">⚠️ Belum ada semester aktif — Menampilkan semua jadwal</span>
        <a href="{{ route('baak.semester.index') }}" class="text-xs text-yellow-700 hover:text-yellow-900 underline font-semibold">Set Semester →</a>
    </div>
    @endif

    {{-- Stats Bar --}}
    <div class="grid grid-cols-4 gap-4 mb-5">
        @foreach([
            ['label'=>'Total Jadwal','value'=>$stats['jadwal'],'color'=>'indigo','icon'=>'📅'],
            ['label'=>'Dosen Aktif','value'=>$stats['dosen'],'color'=>'emerald','icon'=>'👨‍🏫'],
            ['label'=>'Mata Kuliah','value'=>$stats['matakuliah'],'color'=>'amber','icon'=>'📚'],
            ['label'=>'Ruangan','value'=>$stats['ruangan'],'color'=>'sky','icon'=>'🏫'],
        ] as $stat)
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="text-2xl mb-1">{{ $stat['icon'] }}</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</div>
            <div class="text-xs text-gray-500">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Clash Error --}}
    @if($errors->has('clash'))
    <div class="mb-4 bg-red-50 border-2 border-red-400 text-red-800 px-4 py-3 rounded-xl text-sm font-medium">
        ⚠️ {{ $errors->first('clash') }}
    </div>
    @endif

    {{-- Split-screen 70 / 30 --}}
    <div class="flex gap-5 h-[calc(100vh-200px)]">

        {{-- ── Left 70%: Jadwal Table ──────────────────────────────────── --}}
        <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800">📋 Input Jadwal Perkuliahan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Format tabel disamakan dengan papan Live Jadwal.</p>
                </div>
                <span id="realtime-badge" class="text-xs text-green-600 flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse inline-block"></span> Live
                </span>
            </div>
            <div class="overflow-auto flex-1">
                <table class="w-full text-sm" id="jadwal-table">
                    <thead class="sticky top-0 bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">NO</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">SMT</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">HARI</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">WAKTU</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">RUANG</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">KODE</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">MATA KULIAH</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">SKS</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">DOSEN</th>
                            <th class="px-3 py-2 font-medium text-gray-600">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="jadwal-tbody" class="divide-y divide-gray-50">
                        @forelse($jadwal as $j)
                        <tr data-id="{{ $j->id }}" class="hover:bg-gray-50 transition">
                            <td class="px-3 py-2 text-gray-500 text-xs">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $j->semester }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($j->hari) {
                                        'Senin'  =>'bg-blue-100 text-blue-700',
                                        'Selasa' =>'bg-purple-100 text-purple-700',
                                        'Rabu'   =>'bg-green-100 text-green-700',
                                        'Kamis'  =>'bg-yellow-100 text-yellow-700',
                                        'Jumat'  =>'bg-orange-100 text-orange-700',
                                        default  =>'bg-gray-100 text-gray-700',
                                    } }}">{{ $j->hari }}</span>
                            </td>
                            <td class="px-3 py-2 text-gray-600 text-xs">
                                {{ \Carbon\Carbon::parse($j->waktu_mulai)->format('H:i') }} –
                                {{ \Carbon\Carbon::parse($j->waktu_selesai)->format('H:i') }}
                            </td>
                            <td class="px-3 py-2 text-gray-600 text-xs">
                                <span class="font-medium">{{ $j->ruangan?->kode }}</span>
                                <span class="block text-gray-400">{{ $j->ruangan?->nama }}</span>
                            </td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $j->matakuliah?->kode ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-800 text-xs">
                                <span class="block font-medium">{{ $j->matakuliah?->nama }}</span>
                                <span class="text-gray-400">{{ $j->kelas?->nama ? $j->kelas->nama . ' · ' : '' }}{{ $j->prodi }}</span>
                            </td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $j->matakuliah?->sks ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $j->dosen?->nama }}</td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex gap-1 justify-center">
                                    <button onclick="editJadwal({{ $j->id }}, {{ $j->toJson() }})"
                                        class="text-xs px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded transition">Edit</button>
                                    <form method="POST" action="{{ route('baak.jadwal.destroy', $j->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs px-2 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400 italic">Belum ada jadwal. Tambahkan melalui form di sebelah kanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Right 30%: Sticky Form ───────────────────────────────────── --}}
        <div class="w-96 flex-shrink-0 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 id="form-title" class="font-semibold text-gray-800">➕ Input Jadwal BAAK</h2>
            </div>
            <div class="overflow-y-auto flex-1 p-4">
                <form id="jadwal-form" method="POST" action="{{ route('baak.jadwal.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <input type="hidden" name="jadwal_id" id="form-jadwal-id">

                    @if($errors->any() && !$errors->has('clash'))
                    <div class="bg-red-50 border border-red-200 text-red-700 text-xs p-2 rounded">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Semester Akademik Aktif --}}
                    <div>
                        <label class="text-xs font-medium text-gray-700">Semester Akademik</label>
                        @if($semesterAktif)
                            <div class="mt-1 w-full border border-emerald-200 bg-emerald-50 rounded-lg px-3 py-2 text-sm flex items-center justify-between">
                                <span class="text-emerald-700 font-semibold">📅 {{ $semesterAktif->nama }}</span>
                                <span class="text-xs bg-emerald-500 text-white px-1.5 py-0.5 rounded-full">AKTIF</span>
                            </div>
                        @else
                            <div class="mt-1 w-full border border-yellow-200 bg-yellow-50 rounded-lg px-3 py-2 text-sm text-yellow-700">
                                ⚠️ Belum ada semester aktif — <a href="{{ route('baak.semester.index') }}" class="underline font-semibold">Set di sini</a>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Mata Kuliah</label>
                        <button type="button" id="picker-btn-matakuliah" onclick="openPicker('matakuliah')"
                            class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <span id="picker-label-matakuliah" class="text-gray-400 truncate">— pilih mata kuliah —</span>
                            <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">🔍 Pilih</span>
                        </button>
                        <input type="hidden" name="matakuliah_id" id="picker-value-matakuliah">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Kode</label>
                            <input type="text" id="matakuliah-kode-preview"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600 focus:outline-none"
                                placeholder="Otomatis dari master MK" readonly>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">SKS</label>
                            <input type="text" id="matakuliah-sks-preview"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600 focus:outline-none"
                                placeholder="Otomatis dari master MK" readonly>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Dosen</label>
                        <button type="button" id="picker-btn-dosen" onclick="openPicker('dosen')"
                            class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <span id="picker-label-dosen" class="text-gray-400 truncate">— pilih dosen —</span>
                            <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">🔍 Pilih</span>
                        </button>
                        <input type="hidden" name="dosen_id" id="picker-value-dosen">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Kelas</label>
                        <button type="button" id="picker-btn-kelas" onclick="openPicker('kelas')"
                            class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <span id="picker-label-kelas" class="text-gray-400 truncate">— pilih kelas —</span>
                            <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">🔍 Pilih</span>
                        </button>
                        <input type="hidden" name="kelas_id" id="picker-value-kelas">
                        @if($kelas->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">Belum ada master kelas. Tambahkan dulu lewat menu Master Kelas.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Prodi</label>
                            <button type="button" id="picker-btn-prodi" onclick="openPicker('prodi')"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                                <span id="picker-label-prodi" class="text-gray-400 truncate">— pilih prodi —</span>
                                <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">▼</span>
                            </button>
                            <input type="hidden" name="prodi" id="picker-value-prodi" value="{{ old('prodi') }}" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">SMT</label>
                            <input type="text" name="semester" id="semester-input" value="{{ old('semester') }}"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600 focus:outline-none" placeholder="Otomatis dari master kelas" readonly required>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Hari</label>
                        <button type="button" id="picker-btn-hari" onclick="openPicker('hari')"
                            class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <span id="picker-label-hari" class="text-gray-400 truncate">— pilih hari —</span>
                            <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">▼</span>
                        </button>
                        <input type="hidden" name="hari" id="picker-value-hari" value="{{ old('hari') }}" required>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Ruangan</label>
                        <button type="button" id="picker-btn-ruangan" onclick="openPicker('ruangan')"
                            class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between hover:border-emerald-400 transition focus:ring-2 focus:ring-emerald-400 focus:outline-none bg-white">
                            <span id="picker-label-ruangan" class="text-gray-400 truncate">— pilih ruangan —</span>
                            <span class="ml-2 text-gray-400 flex-shrink-0 text-xs">🔍 Pilih</span>
                        </button>
                        <input type="hidden" name="ruangan_id" id="picker-value-ruangan">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Mulai</label>
                            <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai','08:00') }}"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">Selesai</label>
                            <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai','10:30') }}"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">
                            💾 Simpan
                        </button>
                        <button type="button" id="btn-cancel" onclick="resetForm()" class="hidden px-3 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Picker Modal ─────────────────────────────────────────────────────── --}}
<div id="picker-modal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center" onclick="closePicker(event)">
    <div class="bg-white rounded-xl shadow-xl w-80 flex flex-col max-h-[60vh]" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <h3 id="picker-title" class="font-semibold text-gray-800 text-sm">Pilih...</h3>
            <button type="button" onclick="closePicker()" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition text-base leading-none">×</button>
        </div>
        {{-- Search --}}
        <div class="px-3 py-2 border-b border-gray-50 flex-shrink-0">
            <input type="text" id="picker-search"
                placeholder="Cari..."
                oninput="filterPicker(this.value)"
                class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
        </div>
        {{-- List --}}
        <div id="picker-list" class="overflow-y-auto flex-1 p-1.5">
            {{-- Populated by JS --}}
        </div>
    </div>
</div>

@php
$pickerMatakuliah = $matakuliah->map(fn($m) => [
    'id'    => $m->id,
    'label' => ($m->kode ? $m->kode . ' — ' : '') . $m->nama . ($m->sks ? ' (' . $m->sks . ' SKS)' : ''),
    'meta'  => ($m->kode ?? '') . ($m->sks ? ' · ' . $m->sks . ' SKS' : ''),
    'kode'  => $m->kode,
    'sks'   => $m->sks,
])->values();

$pickerDosen = $dosen->map(fn($d) => [
    'id'    => $d->id,
    'label' => $d->nama,
    'meta'  => $d->nidn ?? '',
])->values();

$pickerKelas = $kelas->map(fn($k) => [
    'id'       => $k->id,
    'label'    => $k->nama,
    'meta'     => 'Semester ' . $k->semester,
    'semester' => $k->semester,
])->values();

$pickerRuangan = $ruangan->map(fn($r) => [
    'id'    => $r->id,
    'label' => $r->kode . ' — ' . $r->nama,
    'meta'  => $r->jenis . ' · ' . $r->kapasitas . ' kursi',
    'kode'  => $r->kode,
])->values();
@endphp

@push('scripts')
<script src="{{ mix('js/app.js') }}"></script>
@endpush

<script>
// ── Picker data dari PHP ──────────────────────────────────────────────────
const PICKER_DATA = {
    matakuliah : @json($pickerMatakuliah),
    dosen      : @json($pickerDosen),
    kelas      : @json($pickerKelas),
    ruangan    : @json($pickerRuangan),
    prodi      : [
        { id: 'Teknik Informatika', label: 'Teknik Informatika', meta: '' },
        { id: 'Sistem Informasi',   label: 'Sistem Informasi',   meta: '' },
        { id: 'Matematika',         label: 'Matematika',         meta: '' },
        { id: 'Manajemen',          label: 'Manajemen',          meta: '' },
    ],
    hari       : [
        { id: 'Senin',  label: 'Senin',  meta: '' },
        { id: 'Selasa', label: 'Selasa', meta: '' },
        { id: 'Rabu',   label: 'Rabu',   meta: '' },
        { id: 'Kamis',  label: 'Kamis',  meta: '' },
        { id: 'Jumat',  label: 'Jumat',  meta: '' },
        { id: 'Sabtu',  label: 'Sabtu',  meta: '' },
    ],
};

const PICKER_CONFIG = {
    matakuliah : { title: 'Pilih Mata Kuliah', sub: 'Ketik kode atau nama mata kuliah' },
    dosen      : { title: 'Pilih Dosen',       sub: 'Ketik nama dosen' },
    kelas      : { title: 'Pilih Kelas',        sub: 'Ketik nama atau semester kelas' },
    ruangan    : { title: 'Pilih Ruangan',      sub: 'Ketik kode atau nama ruangan' },
    prodi      : { title: 'Pilih Prodi',        sub: 'Pilih program studi' },
    hari       : { title: 'Pilih Hari',         sub: 'Pilih hari perkuliahan' },
};

let currentPicker = null;

// ── Core picker functions ────────────────────────────────────────────────
const PICKER_INITIAL_LIMIT = 5;
let pickerSearchActive = false;

function openPicker(type) {
    currentPicker = type;
    pickerSearchActive = false;
    const cfg = PICKER_CONFIG[type];
    document.getElementById('picker-title').textContent = cfg.title;
    document.getElementById('picker-search').value = '';
    renderPickerList(PICKER_DATA[type], false);
    document.getElementById('picker-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('picker-search').focus(), 80);
}

function closePicker(event) {
    if (event && event.target !== document.getElementById('picker-modal')) return;
    document.getElementById('picker-modal').classList.add('hidden');
    currentPicker = null;
}

function filterPicker(q) {
    const lower = q.trim().toLowerCase();
    pickerSearchActive = lower.length > 0;
    const filtered = PICKER_DATA[currentPicker].filter(i =>
        i.label.toLowerCase().includes(lower) || (i.meta || '').toLowerCase().includes(lower)
    );
    renderPickerList(filtered, pickerSearchActive);
}

function renderPickerList(items, showAll = false) {
    const list = document.getElementById('picker-list');
    if (!items.length) {
        list.innerHTML = `<div class="text-center text-gray-400 py-10">
            <div class="text-3xl mb-2">🔎</div>
            <p class="text-sm">Tidak ada data yang cocok.</p>
        </div>`;
        return;
    }
    const selectedId = document.getElementById(`picker-value-${currentPicker}`)?.value;
    const totalCount = items.length;
    const displayItems = showAll ? items : items.slice(0, PICKER_INITIAL_LIMIT);
    const remaining = totalCount - displayItems.length;

    let html = displayItems.map(item => {
        const isSelected = String(item.id) === String(selectedId);
        return `<button type="button" onclick="selectPicker('${item.id}')"
            class="w-full text-left px-3 py-2 rounded-lg transition flex items-center justify-between gap-2
                   ${isSelected ? 'bg-emerald-50 border border-emerald-200' : 'hover:bg-gray-50'}">
            <div class="min-w-0">
                <div class="text-sm text-gray-800 truncate">${item.label}</div>
                ${item.meta ? `<div class="text-xs text-gray-400">${item.meta}</div>` : ''}
            </div>
            ${isSelected ? '<span class="text-emerald-500 flex-shrink-0">✓</span>' : ''}
        </button>`;
    }).join('');

    if (remaining > 0) {
        html += `<div class="text-center py-3 text-xs text-gray-400 border-t border-gray-100 mt-1">
            +${remaining} lainnya — ketik untuk mencari
        </div>`;
    }

    list.innerHTML = html;
}

function selectPicker(id) {
    const type = currentPicker;
    const item = PICKER_DATA[type].find(i => String(i.id) === String(id));
    if (!item) return;
    setPicker(type, id);
    document.getElementById('picker-modal').classList.add('hidden');
    currentPicker = null;
}

function setPicker(type, id) {
    const item = PICKER_DATA[type].find(i => String(i.id) === String(id));
    if (!item) return;
    document.getElementById(`picker-value-${type}`).value = id;
    const labelEl = document.getElementById(`picker-label-${type}`);
    labelEl.textContent = item.label;
    labelEl.classList.remove('text-gray-400');
    labelEl.classList.add('text-gray-800');
    if (type === 'matakuliah') {
        document.getElementById('matakuliah-kode-preview').value = item.kode || '';
        document.getElementById('matakuliah-sks-preview').value  = item.sks  || '';
    }
    if (type === 'kelas') {
        document.getElementById('semester-input').value = item.semester || '';
    }
}

// Close on ESC key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('picker-modal').classList.add('hidden');
        currentPicker = null;
    }
});

// ── Form edit / reset ────────────────────────────────────────────────────
const formEl     = document.getElementById('jadwal-form');
const methodEl   = document.getElementById('form-method');
const jadwalIdEl = document.getElementById('form-jadwal-id');
const formTitle  = document.getElementById('form-title');
const btnCancel  = document.getElementById('btn-cancel');

function editJadwal(id, data) {
    formTitle.textContent = '✏️ Edit Jadwal';
    methodEl.value        = 'PUT';
    jadwalIdEl.value      = id;
    formEl.action         = `/baak/jadwal/${id}`;
    btnCancel.classList.remove('hidden');

    setPicker('matakuliah', data.matakuliah_id);
    setPicker('dosen',      data.dosen_id);
    setPicker('ruangan',    data.ruangan_id);
    if (data.kelas_id) setPicker('kelas', data.kelas_id);

    setPicker('prodi', data.prodi);
    setPicker('hari',  data.hari);
    formEl.querySelector('[name=waktu_mulai]').value  = data.waktu_mulai.substring(0, 5);
    formEl.querySelector('[name=waktu_selesai]').value = data.waktu_selesai.substring(0, 5);

    formEl.scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    formTitle.textContent = '➕ Input Jadwal BAAK';
    methodEl.value        = 'POST';
    formEl.action         = '{{ route("baak.jadwal.store") }}';
    formEl.reset();
    btnCancel.classList.add('hidden');

    ['matakuliah','dosen','kelas','ruangan','prodi','hari'].forEach(type => {
        document.getElementById(`picker-value-${type}`).value = '';
        const labelEl = document.getElementById(`picker-label-${type}`);
        const labels = { matakuliah: 'mata kuliah', prodi: 'prodi', hari: 'hari' };
        labelEl.textContent = `— pilih ${labels[type] || type} —`;
        labelEl.classList.add('text-gray-400');
        labelEl.classList.remove('text-gray-800');
    });
    document.getElementById('matakuliah-kode-preview').value = '';
    document.getElementById('matakuliah-sks-preview').value  = '';
    document.getElementById('semester-input').value          = '';
}

// ── Restore old() values after validation failure ────────────────────────
@if(old('matakuliah_id')) setPicker('matakuliah', {{ old('matakuliah_id') }}); @endif
@if(old('dosen_id'))      setPicker('dosen',      {{ old('dosen_id') }});      @endif
@if(old('kelas_id'))      setPicker('kelas',      {{ old('kelas_id') }});      @endif
@if(old('ruangan_id'))    setPicker('ruangan',    {{ old('ruangan_id') }});    @endif
@if(old('prodi'))         setPicker('prodi',      '{{ old('prodi') }}');       @endif
@if(old('hari'))          setPicker('hari',       '{{ old('hari') }}');        @endif

// ── Real-time Echo listener ──────────────────────────────────────────────
if (window.Echo) {
    window.Echo.channel('jadwal')
        .listen('.JadwalCreated', () => location.reload())
        .listen('.JadwalUpdated', () => location.reload())
        .listen('.JadwalDeleted', (e) => {
            const row = document.querySelector(`tr[data-id="${e.jadwal_id}"]`);
            if (row) row.remove();
        });
}
</script>
</x-baak-layout>
