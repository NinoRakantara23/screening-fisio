@extends('layouts.app')

@section('title', 'Edit Atlet - ' . $athlete->name)
@section('page-title', 'Edit Data Atlet')

@section('content')
    @php
        $positions = \App\Models\Athlete::POSITIONS;
        $selectedPosition = old('position', $athlete->display_position);
        $birthDate = $athlete->birth_date
            ? \Carbon\Carbon::parse($athlete->birth_date)->format('Y-m-d')
            : '';
    @endphp

    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('athletes.show', $athlete) }}" class="text-sm font-semibold transition hover:opacity-80" style="color:#00E676">
                ← Kembali ke Detail Atlet
            </a>
            <h1 class="font-display mt-3 text-2xl tracking-widest text-white">Edit Data Atlet</h1>
            <p class="mt-1 text-sm" style="color:#7A8D9E">Perbarui informasi {{ $athlete->name }}.</p>
        </div>

        <form method="POST" action="{{ route('athletes.update', $athlete) }}" enctype="multipart/form-data"
            class="rounded-xl p-6 md:p-8" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold text-white">Nama Lengkap *</label>
                    <input id="name" name="name" type="text" required maxlength="255"
                        value="{{ old('name', $athlete->name) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="position" class="mb-2 block text-sm font-semibold text-white">Posisi *</label>
                    <select id="position" name="position" required
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                        <option value="">Pilih Posisi</option>
                        @if ($selectedPosition && !in_array($selectedPosition, $positions, true))
                            <option value="{{ $selectedPosition }}" selected>{{ $selectedPosition }}</option>
                        @endif
                        @foreach ($positions as $position)
                            <option value="{{ $position }}" {{ $selectedPosition === $position ? 'selected' : '' }}>{{ $position }}</option>
                        @endforeach
                    </select>
                    @error('position') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="gender" class="mb-2 block text-sm font-semibold text-white">Jenis Kelamin</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="M" {{ old('gender', $athlete->gender) === 'M' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="F" {{ old('gender', $athlete->gender) === 'F' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="birth_place" class="mb-2 block text-sm font-semibold text-white">Tempat Lahir</label>
                    <input id="birth_place" name="birth_place" type="text" maxlength="100"
                        value="{{ old('birth_place', $athlete->birth_place) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('birth_place') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="birth_date" class="mb-2 block text-sm font-semibold text-white">Tanggal Lahir</label>
                    <input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}"
                        value="{{ old('birth_date', $birthDate) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('birth_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="age" class="mb-2 block text-sm font-semibold text-white">Usia (tahun)</label>
                    <input id="age" name="age" type="number" min="0" max="100"
                        value="{{ old('age', $athlete->age) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('age') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="height" class="mb-2 block text-sm font-semibold text-white">Tinggi Badan (cm)</label>
                    <input id="height" name="height" type="number" min="0" step="0.1"
                        value="{{ old('height', $athlete->height) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('height') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="weight" class="mb-2 block text-sm font-semibold text-white">Berat Badan (kg)</label>
                    <input id="weight" name="weight" type="number" min="0" step="0.1"
                        value="{{ old('weight', $athlete->weight) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('weight') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="phone" class="mb-2 block text-sm font-semibold text-white">Nomor Telepon</label>
                    <input id="phone" name="phone" type="tel" maxlength="20"
                        value="{{ old('phone', $athlete->phone) }}"
                        class="w-full rounded-lg px-4 py-3 text-white outline-none focus:ring-1"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); --tw-ring-color:#00E676">
                    @error('phone') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="photo" class="mb-2 block text-sm font-semibold text-white">Foto Atlet</label>
                    @if ($athlete->photo)
                        <p class="mb-2 text-sm" style="color:#7A8D9E">Foto saat ini: {{ basename($athlete->photo) }}</p>
                    @endif
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/gif"
                        class="w-full rounded-lg px-4 py-3 text-white"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2)">
                    <p class="mt-1 text-xs" style="color:#7A8D9E">Opsional. Format JPG, PNG, GIF. Maksimal 2 MB.</p>
                    @error('photo') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('athletes.show', $athlete) }}"
                    class="rounded-lg px-5 py-3 text-center text-sm font-semibold transition hover:bg-opacity-80"
                    style="background:#141B22; color:#AAB7C4">Batal</a>
                <button type="submit"
                    class="rounded-lg px-5 py-3 text-sm font-semibold transition hover:-translate-y-px"
                    style="background:#00E676; color:#080C10">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection