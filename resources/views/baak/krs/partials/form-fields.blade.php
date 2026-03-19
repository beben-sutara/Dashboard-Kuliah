@php
    $formMode = old('form_mode');
    $useOldValues = ($prefix === 'add' && $formMode === 'create') || ($prefix === 'edit' && $formMode === 'edit');
    $selectedMahasiswa = $useOldValues ? old('mahasiswa_id') : null;
    $selectedJadwal = $useOldValues ? old('jadwal_id') : null;
    $semesterAkademik = $useOldValues ? old('semester_akademik') : null;
    $selectedStatus = $useOldValues ? old('status', 'aktif') : 'aktif';
@endphp

<div>
    <label class="text-sm font-medium text-gray-700">Mahasiswa</label>
    <select name="mahasiswa_id" id="{{ $prefix }}-mahasiswa-id"
        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
        <option value="">— pilih mahasiswa —</option>
        @foreach($mahasiswa as $student)
        <option value="{{ $student->id }}" {{ (string) $selectedMahasiswa === (string) $student->id ? 'selected' : '' }}>
            {{ $student->nama }} — {{ $student->nim }}
        </option>
        @endforeach
    </select>
</div>

<div>
    <label class="text-sm font-medium text-gray-700">Jadwal</label>
    <select name="jadwal_id" id="{{ $prefix }}-jadwal-id"
        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
        <option value="">— pilih jadwal —</option>
        @foreach($jadwal as $schedule)
        <option value="{{ $schedule->id }}" {{ (string) $selectedJadwal === (string) $schedule->id ? 'selected' : '' }}>
            {{ $schedule->matakuliah?->nama }} — {{ $schedule->dosen?->nama }} ({{ $schedule->hari }} {{ \Carbon\Carbon::parse($schedule->waktu_mulai)->format('H:i') }})
        </option>
        @endforeach
    </select>
</div>

<div>
    <label class="text-sm font-medium text-gray-700">Semester Akademik</label>
    <input type="text" name="semester_akademik" id="{{ $prefix }}-semester-akademik"
        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
        placeholder="Contoh: Genap 2025/2026" value="{{ $semesterAkademik }}" required>
</div>

<div>
    <label class="text-sm font-medium text-gray-700">Status</label>
    <select name="status" id="{{ $prefix }}-status"
        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
        <option value="aktif" {{ $selectedStatus === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ $selectedStatus === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        <option value="selesai" {{ $selectedStatus === 'selesai' ? 'selected' : '' }}>Selesai</option>
    </select>
</div>
