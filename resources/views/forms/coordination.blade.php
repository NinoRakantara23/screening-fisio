@extends('layouts.app')
@section('title', 'Koordinasi') @section('page-title', 'Koordinasi')
@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>
    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="🌀" title="TES LANGKAH FUKUDA — KOORDINASI"
            subtitle="Mengukur koordinasi gerak dan keseimbangan dinamis" tag="KOORDINASI" tagColor="cyan" />
        <form action="{{ route('forms.coordination.store') }}" method="POST">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />
            <x-section-title title="Hasil Tes Langkah Fukuda" />
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Pengukuran</th>
                            <th>Nilai Normal</th>
                            <th>Hasil Atlet (°)</th>
                            <th>Interpretasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>50 Langkah</strong></td>
                            <td>&lt; 30°</td>
                            <td><input type="number" name="steps50" value="{{ old('steps50') }}" step="0.1" placeholder="°"
                                    class="table-input"></td>
                            <td>
                                <input type="text" id="steps50Display" class="table-input" readonly>
                                <input type="hidden" name="steps50_result" id="steps50Result"
                                    value="{{ old('steps50_result') }}">
                            </td>
                        </tr>
                        <tr>
                            <td><strong>100 Langkah</strong></td>
                            <td>&lt; 45°</td>
                            <td><input type="number" name="steps100" value="{{ old('steps100') }}" step="0.1"
                                    placeholder="°" class="table-input"></td>
                            <td>
                                <input type="text" id="steps100Display" class="table-input" readonly>
                                <input type="hidden" name="steps100_result" id="steps100Result"
                                    value="{{ old('steps100_result') }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-group mb-4"><label>Arah Putaran</label>
                <select name="rotation_direction">
                    <option value="">Pilih...</option>
                    @foreach(['Kanan', 'Kiri', 'Tidak ada rotasi signifikan'] as $d)
                        <option value="{{ $d }}" {{ old('rotation_direction') == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
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
            const updateResult = (inputName, displayId, resultId, limit) => {
                const value = document.querySelector(`[name="${inputName}"]`).value;
                const result = document.getElementById(resultId);
                const display = document.getElementById(displayId);
                result.value = value === '' ? '' : (Number(value) < limit ? 'normal' : 'abnormal');
                display.value = result.value === '' ? ''
                    : result.value === 'normal' ? 'Normal' : 'Tidak Normal';
            };
            const update = () => {
                updateResult('steps50', 'steps50Display', 'steps50Result', 30);
                updateResult('steps100', 'steps100Display', 'steps100Result', 45);
            };

            ['steps50', 'steps100'].forEach(name =>
                document.querySelector(`[name="${name}"]`).addEventListener('input', update)
            );
            update();
        });
    </script>
@endpush