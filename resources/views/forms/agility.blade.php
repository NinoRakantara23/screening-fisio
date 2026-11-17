@extends('layouts.app')
@section('title', 'Tes Kelincahan')
@section('page-title', 'Tes Kelincahan')

@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali ke Semua Tes</a>

    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="🔄" title="TES KELINCAHAN"
            subtitle="Tes Hexagonal (3A) dan Tes Illinois (3B)"
            tag="KELINCAHAN" tagColor="blue" />

        <form action="{{ route('forms.agility.store') }}" method="POST">
            @csrf
            <x-athlete-identity :athletes="$athletes ?? []" />

            {{-- === HEXAGONAL === --}}
            <x-section-title title="3A — Tes Kelincahan Hexagonal" />
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="form-group">
                    <label>Waktu Tes 1 (dtk)</label>
                    <input type="number" name="hex_time1" value="{{ old('hex_time1') }}" step="0.01" placeholder="detik"
                        id="hexT1">
                </div>
                <div class="form-group">
                    <label>Waktu Tes 2 (dtk)</label>
                    <input type="number" name="hex_time2" value="{{ old('hex_time2') }}" step="0.01" placeholder="detik"
                        id="hexT2">
                </div>
                <div class="form-group">
                    <label>Nilai Rata-rata</label>
                    <input type="number" name="hex_avg" value="{{ old('hex_avg') }}" step="0.01" placeholder="otomatis"
                        id="hexAvg" readonly style="border-color:#00E676; opacity:0.8">
                </div>
                <div class="form-group">
                    <label>Penilaian Otomatis</label>
                    <input type="text" name="hex_rating" id="hexRating" value="{{ old('hex_rating') }}" readonly>
                </div>
            </div>
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Penilaian</th>
                            <th>Pria (detik)</th>
                            <th>Wanita (detik)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                                ['Luar Biasa', '< 11.2', '< 12.2', 'excellent'],
                                ['Diatas Rata-rata', '11.2 – 13.3', '12.2 – 15.3', 'good'],
                                ['Rata-rata', '13.4 – 15.5', '15.4 – 18.5', 'average'],
                                ['Di bawah Rata-rata', '15.6 – 17.8', '18.6 – 21.8', 'below'],
                                ['Rendah', '> 17.8', '> 21.8', 'poor'],
                            ] as [$r, $p, $w, $b])
                            <tr><td><x-rating-badge :rating="$b" /></td><td>{{ $p }}</td><td>{{ $w }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- === ILLINOIS === --}}
            <x-section-title title="3B — Tes Kelincahan Illinois" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-group">
                    <label>Waktu Tempuh (detik)</label>
                    <input type="number" name="ill_time" value="{{ old('ill_time') }}" step="0.01" placeholder="detik">
            </div>
                <div class="form-group">
                    <label>Penilaian Otomatis</label>
                    <input type="text" name="ill_rating" id="illRating" value="{{ old('ill_rating') }}" readonly>
                </div>
            </div>




                                <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead><tr><th>Penilaian</th><th>Skor Pria</th><th>Skor Wanita</th></tr></thead>
                    <tbody>
                        @foreach([
                                ['Excellent', '< 15.2', '< 17.0', 'excellent'],
                                ['Above Average', '15.2 – 16.1', '17.0 – 17.9', 'good'],
                                ['Average', '16.2 – 18.1', '18.0 – 21.7', 'average'],
                                ['Below Average', '18.2 – 18.2', '21.8 – 23.0', 'below'],
                                ['Poor', '> 18.3', '> 23.0', 'poor'],
                            ] as [$r, $p, $w, $b])
                            <tr><td><x-rating-badge :rating="$b" /></td><td>{{ $p }}</td><td>{{ $w }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-section-title title="Kesimpulan" />
            <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
                <textarea name="conclusion" rows="3" class="w-full bg-transparent border-none outline-none text-sm resize-none"
                          placeholder="Tuliskan kesimpulan hasil tes kelincahan atlet...">{{ old('conclusion') }}</textarea>
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
            const hexT1 = document.getElementById('hexT1');
            const hexT2 = document.getElementById('hexT2');
            const hexAvg = document.getElementById('hexAvg');
            const illTime = document.querySelector('[name="ill_time"]');

            const getRating = (time, thresholds, labels) => {
                if (time === '') return '';
                const value = Number(time);
                const index = thresholds.findIndex((limit, position) =>
                    position === 0 ? value < limit : value <= limit
                );
                return labels[index === -1 ? labels.length - 1 : index];
            };

            const updateRatings = () => {
                const first = hexT1.value;
                const second = hexT2.value;
                const average = first !== '' && second !== ''
                    ? ((Number(first) + Number(second)) / 2).toFixed(2)
                    : '';
                hexAvg.value = average;

                const male = gender.value === 'putra';
                document.getElementById('hexRating').value = getRating(
                    average,
                    male ? [11.2, 13.3, 15.5, 17.8] : [12.2, 15.3, 18.5, 21.8],
                    ['Luar Biasa', 'Diatas Rata-rata', 'Rata-rata', 'Di bawah rata-rata', 'Rendah']
                );
                document.getElementById('illRating').value = getRating(
                    illTime.value,
                    male ? [15.2, 16.1, 18.1, 18.2] : [17.0, 17.9, 21.7, 23.0],
                    ['Sangat Baik', 'Di Atas Rata-rata', 'Rata-rata', 'Di Bawah Rata-rata', 'Buruk']
                );
            };

            [hexT1, hexT2, illTime].forEach(input => input.addEventListener('input', updateRatings));
            gender.addEventListener('change', updateRatings);
            updateRatings();
        });
    </script>
@endpush
