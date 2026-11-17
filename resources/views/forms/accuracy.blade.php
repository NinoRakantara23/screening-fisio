@extends('layouts.app')
@section('title', 'Akurasi Tendangan') @section('page-title', 'Akurasi Tendangan')
@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>
    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="🎯" title="TES KETEPATAN TENDANGAN"
            subtitle="Mengukur ketepatan tendangan ke sasaran gawang dari jarak 16,5 meter (3 percobaan)" tag="KETERAMPILAN"
            tagColor="purple" />
        <form action="{{ route('forms.accuracy.store') }}" method="POST">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />
            <x-section-title title="Hasil Tendangan (3 Percobaan)" />
            <p class="text-xs mb-3" style="color:#7A8D9E">Poin dihitung otomatis: mengenai sasaran = 2, mengenai tali = 1, gagal/meleset = 0.</p>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Percobaan</th>
                            <th>Skor</th>
                            <th>Waktu (dtk)</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 3; $i++)
                            <tr>
                                <td class="font-bold font-mono2" style="color:#00E676">{{ $i }}</td>
                                <td><input type="number" id="shot{{ $i }}Score" name="shot{{ $i }}_score"
                                        value="{{ old('shot' . $i . '_score') }}" placeholder="otomatis"
                                        class="table-input" readonly></td>
                                <td><input type="number" name="shot{{ $i }}_time" value="{{ old('shot' . $i . '_time') }}"
                                        step="0.1" placeholder="detik" class="table-input"></td>
                                <td>
                                    <select name="shot{{ $i }}_note" class="table-select">
                                        <option value="">Pilih...</option>
                                        <option value="sasaran" {{ old('shot' . $i . '_note') == 'sasaran' ? 'selected' : '' }}>Mengenai
                                            sasaran</option>
                                        <option value="tali" {{ old('shot' . $i . '_note') == 'tali' ? 'selected' : '' }}>Mengenai tali
                                        </option>
                                        <option value="gagal" {{ old('shot' . $i . '_note') == 'gagal' ? 'selected' : '' }}>Gagal/Meleset
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-group"><label>Total Skor Otomatis</label><input type="number" id="accuracyTotal"
                        name="total_score" value="{{ old('total_score') }}" readonly></div>
                <div class="form-group"><label>Penilaian Otomatis</label>
                    <input type="text" id="accuracyRating" name="rating" value="{{ old('rating') }}" readonly>
                </div>
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
            const notes = [1, 2, 3].map(attempt =>
                document.querySelector(`[name="shot${attempt}_note"]`)
            );
            const total = document.getElementById('accuracyTotal');
            const rating = document.getElementById('accuracyRating');
            const labels = {
                Excellent: 'Sangat Baik',
                Good: 'Baik',
                Average: 'Rata-rata',
                Weak: 'Lemah',
                Poor: 'Buruk',
            };

            const updateRating = () => {
                const values = notes.map(note => note.value);
                values.forEach((value, index) => {
                    document.getElementById(`shot${index + 1}Score`).value =
                        value === '' ? '' : ({ sasaran: 2, tali: 1, gagal: 0 })[value];
                });
                if (values.some(value => value === '')) {
                    total.value = '';
                    rating.value = '';
                    return;
                }

                const score = values.reduce((sum, value) =>
                    sum + ({ sasaran: 2, tali: 1, gagal: 0 })[value], 0
                );
                total.value = score;
                const category = score >= 5 ? 'Excellent'
                    : score === 4 ? 'Good'
                    : score === 3 ? 'Average'
                    : score === 2 ? 'Weak'
                    : 'Poor';
                rating.value = labels[category];
            };

            notes.forEach(note => note.addEventListener('change', updateRating));
            updateRating();
        });
    </script>
@endpush