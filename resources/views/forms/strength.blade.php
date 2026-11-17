{{-- ============================================================
     resources/views/forms/strength.blade.php
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Tes Kekuatan')
@section('page-title', 'Tes Kekuatan')

@section('content')
<a href="{{ route('tests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
   style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali ke Semua Tes</a>

<div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <x-form-header icon="💪" title="TES KEKUATAN — TEKAN TUBUH & DUDUK-BARING"
        subtitle="Mengukur kekuatan otot dada, bahu, lengan, dan otot inti"
        tag="KEKUATAN" tagColor="red" />

    <form action="{{ route('forms.strength.store') }}" method="POST">
        @csrf
        <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />

        {{-- Tekan tubuh --}}
        <x-section-title title="Tekan Tubuh" />
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm norma-table">
                <thead><tr><th>Skor</th><th>Putra (repetisi)</th><th>Penilaian</th><th>Putri (repetisi)</th></tr></thead>
                <tbody>
                    @foreach([
                        ['5','≥ 38 kali','Sempurna','≥ 21 kali','excellent'],
                        ['4','29 – 37 kali','Baik Sekali','16 – 20 kali','good'],
                        ['3','20 – 28 kali','Baik','10 – 15 kali','average'],
                        ['2','12 – 19 kali','Cukup','5 – 9 kali','below'],
                        ['1','4 – 11 kali','Kurang','1 – 4 kali','poor'],
                    ] as [$s,$p,$k,$w,$b])
                    <tr>
                        <td class="font-bold font-mono2" style="color:#00E676">{{ $s }}</td>
                        <td>{{ $p }}</td><td><x-rating-badge :rating="$b" /></td><td>{{ $w }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            <div class="form-group"><label>Jumlah Tekan Tubuh (repetisi)</label><input type="number" id="pushupCount" name="pushup_count" value="{{ old('pushup_count') }}" min="0" placeholder="repetisi"></div>
            <div class="form-group"><label>Skor (1–5)</label><input type="number" name="pushup_score" id="pushupScore" value="{{ old('pushup_score') }}" readonly></div>
            <div class="form-group"><label>Penilaian Otomatis</label>
                <input type="text" name="pushup_rating" id="pushupRating" value="{{ old('pushup_rating') }}" readonly>
            </div>
        </div>
        <div class="form-group mb-2"><label>Catatan Tekan Tubuh</label><textarea name="pushup_note" rows="2" placeholder="Catatan teknis...">{{ old('pushup_note') }}</textarea></div>

        {{-- Duduk-baring --}}
        <x-section-title title="Duduk-Baring" />
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm norma-table">
                <thead><tr><th>Skor</th><th>Putra (repetisi)</th><th>Penilaian</th><th>Putri (repetisi)</th></tr></thead>
                <tbody>
                    @foreach([
                        ['5','> 90 kali','Sempurna','> 88 kali','excellent'],
                        ['4','70 – 89 kali','Baik Sekali','69 – 87 kali','good'],
                        ['3','50 – 69 kali','Baik','48 – 68 kali','average'],
                        ['2','30 – 49 kali','Cukup','29 – 47 kali','below'],
                        ['1','10 – 29 kali','Kurang','10 – 28 kali','poor'],
                    ] as [$s,$p,$k,$w,$b])
                    <tr>
                        <td class="font-bold font-mono2" style="color:#00E676">{{ $s }}</td>
                        <td>{{ $p }}</td><td><x-rating-badge :rating="$b" /></td><td>{{ $w }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            <div class="form-group"><label>Jumlah Duduk-Baring (repetisi)</label><input type="number" id="situpCount" name="situp_count" value="{{ old('situp_count') }}" min="0" placeholder="repetisi"></div>
            <div class="form-group"><label>Skor (1–5)</label><input type="number" name="situp_score" id="situpScore" value="{{ old('situp_score') }}" readonly></div>
            <div class="form-group"><label>Penilaian Otomatis</label>
                <input type="text" name="situp_rating" id="situpRating" value="{{ old('situp_rating') }}" readonly>
            </div>
        </div>
        <div class="form-group mb-2"><label>Catatan Duduk-Baring</label><textarea name="situp_note" rows="2" placeholder="Catatan teknis...">{{ old('situp_note') }}</textarea></div>

        <x-section-title title="Kesimpulan" />
        <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
            <textarea name="conclusion" rows="3" class="w-full bg-transparent border-none outline-none text-sm resize-none" placeholder="Kesimpulan...">{{ old('conclusion') }}</textarea>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">💾 Simpan Data</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gender = document.querySelector('[name="gender"]');
            const ratings = ['Sempurna', 'Baik Sekali', 'Baik', 'Cukup', 'Kurang'];
            const updateExercise = (countId, scoreId, ratingId, limits) => {
                const count = document.getElementById(countId).value;
                const scoreInput = document.getElementById(scoreId);
                const ratingInput = document.getElementById(ratingId);
                if (count === '' || !gender.value) {
                    scoreInput.value = '';
                    ratingInput.value = '';
                    return;
                }

                const value = Number(count);
                const index = limits.findIndex(limit => value >= limit);
                const score = index === -1 ? 1 : 5 - index;
                scoreInput.value = score;
                ratingInput.value = ratings[score - 1];
            };

            const updateRatings = () => {
                const male = gender.value === 'putra';
                updateExercise('pushupCount', 'pushupScore', 'pushupRating',
                    male ? [38, 29, 20, 12] : [21, 16, 10, 5]);
                updateExercise('situpCount', 'situpScore', 'situpRating',
                    male ? [90, 70, 50, 30] : [88, 69, 48, 29]);
            };

            ['pushupCount', 'situpCount'].forEach(id =>
                document.getElementById(id).addEventListener('input', updateRatings)
            );
            gender.addEventListener('change', updateRatings);
            updateRatings();
        });
    </script>
@endpush
