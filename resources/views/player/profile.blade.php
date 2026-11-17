@extends('layouts.app')
@section('title', 'Profil Atlet')
@section('page-title', 'Profil Atlet')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="rounded-3xl p-6" style="background:#0B1014; border:1px solid rgba(0,230,118,0.2)">
        <div class="mb-6">
            <h1 class="font-display text-3xl lg:text-4xl tracking-[0.15em] uppercase">{{ $athlete->name }}</h1>
            <p class="mt-2 text-sm" style="color:#7A8D9E">
                Profil atlet, progres screening, dan hasil pemeriksaan fisik.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1.1fr] gap-5">
            <div class="rounded-[2rem] border border-[#00E676]/15 bg-[#0E1419] p-6">
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <div class="w-28 h-28 rounded-[2rem] overflow-hidden border border-[#00E676]/25 bg-[#101820] flex items-center justify-center">
                        @if($athlete->photo)
                            <img src="{{ asset($athlete->photo) }}" alt="{{ $athlete->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-white">{{ strtoupper(substr($athlete->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h2 class="text-3xl font-bold tracking-tight">{{ $athlete->name }}</h2>
                        <p class="mt-3 text-sm" style="color:#7A8D9E">
                            Bergabung: {{ $athlete->created_at?->format('d M Y') ?? '-' }}
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <span class="inline-flex items-center rounded-full bg-[#0A1F2F] px-4 py-2 text-xs font-semibold text-[#8AC7FF] border border-[#8AC7FF]/15">
                                {{ $athlete->position ?? '-' }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-[#0F2810] px-4 py-2 text-xs font-semibold text-[#C4FFD7] border border-[#C4FFD7]/15">
                                {{ $testCount }} Tes Tercatat
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <section>
                <h3 class="text-xs uppercase tracking-[0.24em] mb-4" style="color:#7A8D9E">Data Fisik</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Usia</p>
                        <p class="font-semibold">{{ $athlete->age ? $athlete->age . ' tahun' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Jenis Kelamin</p>
                        <p class="font-semibold">
                            {{ $athlete->gender === 'M' ? 'Laki-laki' : ($athlete->gender === 'F' ? 'Perempuan' : '-') }}
                        </p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Tempat, Tanggal Lahir</p>
                        <p class="font-semibold">
                            {{ ($athlete->birth_place ?? '-') . ', ' . ($athlete->birth_date ? \Carbon\Carbon::parse($athlete->birth_date)->format('d M Y') : '-') }}
                        </p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Nomor Telepon</p>
                        <p class="font-semibold">{{ $athlete->phone ?? '-' }}</p>
                    </div>
                </div>
                <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Tinggi Badan</p>
                    <p class="font-semibold">{{ $athlete->height ? number_format($athlete->height, 1) . ' cm' : '-' }}</p>
                </div>
                <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] mb-2" style="color:#7A8D9E">Berat Badan</p>
                    <p class="font-semibold">{{ $athlete->weight ? number_format($athlete->weight, 1) . ' kg' : '-' }}</p>
                </div>
            </section>
        </div>
    </div>

    @if($errors->any())
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:rgba(0,230,118,0.1); border:1px solid rgba(0,230,118,0.3); color:#00E676">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-3xl rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <h3 class="font-display text-lg tracking-widest mb-5">Perbarui Kontak & Data Fisik</h3>
        <form method="POST" action="{{ route('player.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-group mb-4">
                <label>No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $athlete->phone) }}" required>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="form-group">
                    <label>Tinggi (cm)</label>
                    <input type="number" step="0.1" name="height" value="{{ old('height', $athlete->height) }}">
                </div>
                <div class="form-group">
                    <label>Berat (kg)</label>
                    <input type="number" step="0.1" name="weight" value="{{ old('weight', $athlete->weight) }}">
                </div>
            </div>
            <button type="submit" class="btn-primary px-6 py-3">Simpan</button>
        </form>
        <p class="text-xs mt-4" style="color:#7A8D9E">
            Nama, posisi, foto, dan data kelahiran hanya bisa diubah oleh fisioterapis atau admin.
        </p>
    </div>
</div>
@endsection