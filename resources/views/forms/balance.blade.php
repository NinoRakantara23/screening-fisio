@extends('layouts.app')
@section('title', 'Tes Keseimbangan Y') @section('page-title', 'Tes Keseimbangan Y')

@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>

    <div class="max-w-4xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="⚖️" title="TES KESEIMBANGAN Y"
            subtitle="Keseimbangan dinamis dalam tiga arah: depan (A), belakang-dalam (PM), dan belakang-luar (PL)" tag="KESEIMBANGAN"
            tagColor="green" />

        <form action="{{ route('forms.balance.store') }}" method="POST" id="ybtForm">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />

            {{-- Leg Length --}}
            <div class="form-group mt-2 max-w-xs">
                <label>Panjang Tungkai (cm) <span class="text-red-400">*</span></label>
                <input type="number" name="leg_length" id="legLength" value="{{ old('leg_length') }}" placeholder="cm"
                    required>
            </div>

            <x-section-title title="Pengukuran Keseimbangan Y" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Kaki Kiri --}}
                <div class="rounded-xl p-4" style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">🦵 Kaki Kiri</h4>
                    <div class="form-group"><label>Anterior (cm)</label><input type="number" name="left_a" id="left_a"
                            value="{{ old('left_a') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posteromedial (cm)</label><input type="number" name="left_pm"
                            id="left_pm" value="{{ old('left_pm') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posterolateral (cm)</label><input type="number" name="left_pl"
                            id="left_pl" value="{{ old('left_pl') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                </div>
                {{-- Kaki Kanan --}}
                <div class="rounded-xl p-4" style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">🦵 Kaki Kanan</h4>
                    <div class="form-group"><label>Anterior (cm)</label><input type="number" name="right_a" id="right_a"
                            value="{{ old('right_a') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posteromedial (cm)</label><input type="number" name="right_pm"
                            id="right_pm" value="{{ old('right_pm') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posterolateral (cm)</label><input type="number" name="right_pl"
                            id="right_pl" value="{{ old('right_pl') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                </div>
                {{-- Formula --}}
                <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.2)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">📊 Perhitungan</h4>
                    <div class="text-xs leading-relaxed mb-4 p-3 rounded-lg" style="background:#1C252F; color:#7A8D9E">
                        Skor gabungan (%) =<br>
                        (A + PM + PL) ÷<br>
                        (3 × Panjang Tungkai) × 100
                    </div>
                    <p class="text-xs">Hasil dan penilaian akan diperbarui otomatis saat data diisi.</p>
                </div>
            </div>

            <x-section-title title="Hasil Perhitungan" />
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Arah</th>
                            <th>Kiri</th>
                            <th>Kanan</th>
                            <th>Selisih</th>
                            <th>Standar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['anterior', 'Depan'], ['pm', 'Belakang-dalam'], ['pl', 'Belakang-luar']] as [$k, $label])
                            <tr>
                                <td><strong>{{ $label }}</strong></td>
                                <td><input type="text" name="res_left_{{ $k }}" id="res_left_{{ $k }}"
                                        value="{{ old('res_left_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td><input type="text" name="res_right_{{ $k }}" id="res_right_{{ $k }}"
                                        value="{{ old('res_right_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td><input type="text" name="res_diff_{{ $k }}" id="res_diff_{{ $k }}"
                                        value="{{ old('res_diff_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td>
                                    <input type="text" id="std_{{ $k }}_display" class="table-input" readonly>
                                    <input type="hidden" name="std_{{ $k }}" id="std_{{ $k }}"
                                        value="{{ old('std_' . $k) }}">
                                </td>
                            </tr>
                        @endforeach
                        <tr style="background:rgba(0,230,118,0.05)">
                            <td><strong>Skor Gabungan (%)</strong></td>
                            <td><input type="text" name="composite_left" id="composite_left"
                                    value="{{ old('composite_left') }}" placeholder="%" class="table-input" readonly></td>
                            <td><input type="text" name="composite_right" id="composite_right"
                                    value="{{ old('composite_right') }}" placeholder="%" class="table-input" readonly></td>
                            <td>—</td>
                            <td>
                                <input type="text" id="std_composite_display" class="table-input" readonly>
                                <input type="hidden" name="std_composite" id="std_composite"
                                    value="{{ old('std_composite') }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <x-section-title title="Kesimpulan" />
            <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬
                    Kesimpulan</label>
                <textarea name="conclusion" rows="3"
                    class="w-full bg-transparent border-none outline-none text-sm resize-none"
                    placeholder="Tuliskan kesimpulan hasil tes keseimbangan Y...">{{ old('conclusion') }}</textarea>
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
            const valueOf = id => {
                const value = document.getElementById(id).value;
                return value === '' ? null : Number(value);
            };
            const set = (id, value) => {
                document.getElementById(id).value = value === null ? '' : value;
            };
            const setStandard = (name, value) => {
                set(`std_${name}`, value);
                set(`std_${name}_display`, value === null ? '' : value === 'optimal' ? 'Optimal' : 'Di bawah standar');
            };
            const directions = [
                ['anterior', 'a'],
                ['pm', 'pm'],
                ['pl', 'pl'],
            ];

            const calculate = () => {
                directions.forEach(([direction, suffix]) => {
                    const left = valueOf(`left_${suffix}`);
                    const right = valueOf(`right_${suffix}`);
                    const difference = left === null || right === null ? null : Math.abs(left - right);
                    set(`res_left_${direction}`, left === null ? '' : left.toFixed(1));
                    set(`res_right_${direction}`, right === null ? '' : right.toFixed(1));
                    set(`res_diff_${direction}`, difference === null ? '' : difference.toFixed(1));
                    setStandard(direction === 'anterior' ? direction : suffix,
                        difference === null ? null : difference < 4 ? 'optimal' : 'below');
                });

                const leg = valueOf('legLength');
                const composites = ['left', 'right'].map(side => {
                    const reaches = ['a', 'pm', 'pl'].map(suffix => valueOf(`${side}_${suffix}`));
                    if (leg === null || leg <= 0 || reaches.includes(null)) return null;
                    return reaches.reduce((sum, value) => sum + value, 0) / (3 * leg) * 100;
                });
                composites.forEach((value, index) =>
                    set(`composite_${index === 0 ? 'left' : 'right'}`, value === null ? '' : `${value.toFixed(1)}%`)
                );
                const standard = composites.includes(null)
                    ? null
                    : (composites[0] + composites[1]) / 2 > 89 ? 'optimal' : 'below';
                setStandard('composite', standard);
            };

            ['legLength', 'left_a', 'left_pm', 'left_pl', 'right_a', 'right_pm', 'right_pl']
                .forEach(id => document.getElementById(id).addEventListener('input', calculate));
            calculate();
        });
    </script>
@endpush