@extends('layouts.app')
@section('title', 'Tes Daya Tahan Yo-Yo')
@section('page-title', 'Tes Daya Tahan Yo-Yo')

@section('content')

<a href="{{ route('tests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
   style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
    ← Kembali ke Semua Tes
</a>

<div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">

    <x-form-header icon="🏃" title="TES DAYA TAHAN YO-YO"
        subtitle="Tes pemulihan lari bolak-balik Yo-Yo tingkat 1"
        tag="DAYA TAHAN" tagColor="green" />

    <form action="{{ route('forms.endurance.store') }}" method="POST">
        @csrf

        <x-athlete-identity :athletes="$athletes ?? []" />

        <x-section-title title="Hasil Tes Yo-Yo" />

        {{-- Tabel acuan --}}
        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm norma-table">
                <thead>
                    <tr>
                        <th>Penilaian</th><th>Jarak</th><th>Tingkatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Elite', '> 2400 m', '> 20.1'],
                        ['Excellent', '2000 – 2400 m', '18.7 – 20.1'],
                        ['Good', '1520 – < 2000 m', '17.3 – < 18.7'],
                        ['Average', '1040 – < 1520 m', '15.7 – < 17.3'],
                        ['Below Average', '520 – < 1040 m', '14.2 – < 15.7'],
                        ['Very Poor', '< 520 m', '< 14.2'],
                    ] as [$rating,$meters,$level])
                    <tr>
                        <td><x-rating-badge :rating="$rating" /></td>
                        <td>{{ $meters }}</td>
                        <td>{{ $level }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label>Jarak Tempuh (m) <span class="text-red-400">*</span></label>
                <input type="number" name="distance" value="{{ old('distance') }}"
                       placeholder="meter" required min="0">
            </div>
            <div class="form-group">
                <label>Tingkatan yang Dicapai</label>
                <input type="text" name="level" value="{{ old('level') }}" placeholder="cth: 17.3">
            </div>
            <div class="form-group">
                <label>Penilaian Otomatis</label>
                <input type="text" id="enduranceRatingDisplay" value=""
                    placeholder="Terisi otomatis dari jarak tempuh" readonly>
                <input type="hidden" name="rating" id="enduranceRating" value="{{ old('rating') }}">
            </div>
        </div>
        <p class="text-xs -mt-2 mb-5" style="color:#7A8D9E">
            Penilaian dihitung otomatis berdasarkan jarak tempuh Yo-Yo.
        </p>

        <x-section-title title="Kesimpulan" />
        <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
            <textarea name="conclusion" rows="3"
                      class="w-full bg-transparent border-none outline-none text-sm resize-none"
                      placeholder="Tuliskan kesimpulan hasil tes daya tahan atlet...">{{ old('conclusion') }}</textarea>
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
            const distanceInput = document.querySelector('[name="distance"]');
            const ratingValue = document.getElementById('enduranceRating');
            const ratingDisplay = document.getElementById('enduranceRatingDisplay');

            if (!distanceInput || !ratingValue || !ratingDisplay) return;

            const updateRating = () => {
                const distance = Number(distanceInput.value);
                if (distanceInput.value === '' || !Number.isFinite(distance) || distance < 0) {
                    ratingValue.value = '';
                    ratingDisplay.value = '';
                    return;
                }

                const rating = distance > 2400 ? 'Elite'
                    : distance >= 2000 ? 'Excellent'
                    : distance >= 1520 ? 'Good'
                    : distance >= 1040 ? 'Average'
                    : distance >= 520 ? 'Below Average'
                    : 'Very Poor';
                const labels = {
                    'Elite': 'Elit',
                    'Excellent': 'Sangat Baik',
                    'Good': 'Baik',
                    'Average': 'Rata-rata',
                    'Below Average': 'Di Bawah Rata-rata',
                    'Very Poor': 'Sangat Buruk',
                };
                ratingValue.value = rating;
                ratingDisplay.value = labels[rating];
            };

            distanceInput.addEventListener('input', updateRating);
            updateRating();
        });
    </script>
@endpush
