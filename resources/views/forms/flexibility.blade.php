@extends('layouts.app')
@section('title', 'Kelenturan Pergelangan Kaki') @section('page-title', 'Kelenturan Pergelangan Kaki')
@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>
    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="🦶" title="TES KELENTURAN PERGELANGAN KAKI"
            subtitle="Mengukur kelenturan sendi pergelangan kaki dan kemampuan menekuk kaki ke atas" tag="KELENTURAN"
            tagColor="yellow" />
        <form action="{{ route('forms.flexibility.store') }}" method="POST">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />
            <x-section-title title="Hasil Tes Kelenturan Pergelangan Kaki (3 Percobaan)" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="form-group"><label>Percobaan 1 (cm)</label><input type="number" name="try1"
                        value="{{ old('try1') }}" step="0.1" placeholder="cm"></div>
                <div class="form-group"><label>Percobaan 2 (cm)</label><input type="number" name="try2"
                        value="{{ old('try2') }}" step="0.1" placeholder="cm"></div>
                <div class="form-group"><label>Percobaan 3 (cm)</label><input type="number" name="try3"
                        value="{{ old('try3') }}" step="0.1" placeholder="cm"></div>
                <div class="form-group"><label>Jarak Terbaik (cm) <span class="text-red-400">*</span></label><input
                        type="number" name="best" value="{{ old('best') }}" step="0.1" placeholder="cm"
                        style="border-color:#00E676" required></div>
                <div class="form-group"><label>Penilaian Otomatis</label>
                    <input type="text" id="flexibilityRating" name="rating" value="{{ old('rating') }}" readonly>
                </div>
            </div>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Penilaian</th>
                            <th>Pria (cm)</th>
                            <th>Wanita (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['Excellent', '> 35.00', '> 32.00', 'excellent'], ['Good', '35 – 32.51', '32 – 30.51', 'good'], ['Average', '32.50 – 29.51', '30.50 – 26.51', 'average'], ['Fair', '29.50 – 26.50', '26.50 – 24.25', 'below'], ['Poor', '< 26.50', '< 24.25', 'poor']] as [$r, $m, $w, $b])
                            <tr>
                                <td><x-rating-badge :rating="$b" /></td>
                                <td>{{ $m }}</td>
                                <td>{{ $w }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-section-title title="Kesimpulan" />
            <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬
                    Kesimpulan</label>
                <textarea name="conclusion" rows="3"
                    class="w-full bg-transparent border-none outline-none text-sm resize-none"
                    placeholder="Kesimpulan...">{{ old('conclusion') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6"><a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a><button
                    type="submit" class="btn-primary">💾 Simpan Data</button></div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const best = document.querySelector('[name="best"]');
            const gender = document.querySelector('[name="gender"]');
            const rating = document.getElementById('flexibilityRating');
            const labels = ['Sangat Baik', 'Baik', 'Rata-rata', 'Cukup', 'Buruk'];

            const updateRating = () => {
                if (best.value === '' || !gender.value) {
                    rating.value = '';
                    return;
                }

                const limits = gender.value === 'putra'
                    ? [35, 32.51, 29.51, 26.50]
                    : [32, 30.51, 26.51, 24.25];
                const value = Number(best.value);
                const index = limits.findIndex((limit, position) =>
                    position === 0 ? value > limit : value >= limit
                );
                rating.value = labels[index === -1 ? labels.length - 1 : index];
            };

            best.addEventListener('input', updateRating);
            gender.addEventListener('change', updateRating);
            updateRating();
        });
    </script>
@endpush