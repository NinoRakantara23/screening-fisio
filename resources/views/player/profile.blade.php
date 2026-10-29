@extends('layouts.app')
@section('title', 'Profil Atlet')
@section('page-title', 'Profil Atlet')

@section('content')
<div class="max-w-2xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <h2 class="text-2xl font-bold mb-1" style="color:#00E676">{{ $athlete->name }}</h2>
    <p class="text-sm mb-6" style="color:#7A8D9E">
        Posisi {{ $athlete->position ?? '-' }} · Usia {{ $athlete->age ?? '-' }} Tahun · {{ $athlete->gender ?? '-' }}
    </p>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('player.profile.update') }}">
        @csrf
        @method('PUT')
        <div class="form-group mb-4">
            <label>No. Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $athlete->phone) }}">
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
    <p class="text-xs mt-4" style="color:#7A8D9E">Nama, posisi, dan data lain hanya bisa diubah oleh fisioterapis atau admin.</p>
</div>
@endsection