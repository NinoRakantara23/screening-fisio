@extends('layouts.app')
@section('title', 'Dashboard Saya')
@section('page-title', 'Dashboard Saya')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="rounded-xl p-6 flex items-center justify-between" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold"
                style="background:rgba(0,230,118,0.1); color:#00E676">
                {{ strtoupper(mb_substr($athlete->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold">Halo, {{ $firstName }} 👋</h2>
                <p class="text-sm" style="color:#7A8D9E">
                    Posisi: <strong class="text-white">{{ $athlete->position ?? '-' }}</strong> ·
                    Usia: <strong class="text-white">{{ $athlete->age ?? '-' }} Tahun</strong> ·
                    Tes terakhir: <strong class="text-white">{{ $lastTest?->format('d M Y') ?? '-' }}</strong>
                </p>
            </div>
        </div>
        <div class="text-right">
            <div class="text-4xl font-bold" style="color:#00E676">{{ $grade }}</div>
            <div class="text-xs" style="color:#7A8D9E">Grade Keseluruhan</div>
        </div>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach($stats as $stat)
            <div class="rounded-xl p-4 text-center" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                <div class="text-2xl font-bold" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="text-xs mt-1" style="color:#7A8D9E">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Riwayat tes --}}
        <div class="lg:col-span-2 rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background:#00E676"></span> Riwayat Tes Fisik Saya
                </h3>
                <a href="{{ route('player.tests') }}" class="text-xs" style="color:#00E676">Lihat semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(0,230,118,0.2)">
                            @foreach(['Jenis Tes', 'Hasil', 'Rating', 'Tanggal'] as $head)
                                <th class="text-left py-2 px-2 text-xs uppercase tracking-widest font-mono2" style="color:#7A8D9E">{{ $head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTests as $result)
                            @php $rating = collect($result->display_ratings)->first(); @endphp
                            <tr style="border-bottom:1px solid rgba(0,230,118,0.08)">
                                <td class="py-3 px-2 font-semibold">{{ $result->test_type_label }}</td>
                                <td class="py-3 px-2">{{ collect($result->display_results)->first() ?? '—' }}</td>
                                <td class="py-3 px-2">
                                    @if($rating) <x-rating-badge :rating="$rating" /> @else — @endif
                                </td>
                                <td class="py-3 px-2" style="color:#7A8D9E">{{ $result->test_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center" style="color:#7A8D9E">Belum ada data tes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Progress fisik --}}
        <div class="rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <h3 class="font-display text-lg tracking-widest flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full" style="background:#00E676"></span> Progress Fisik
            </h3>
            <div class="space-y-4">
                @foreach($progress as $item)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span class="font-semibold">{{ $item['percent'] !== null ? $item['percent'] . '%' : '—' }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full" style="background:rgba(0,230,118,0.1)">
                            <div class="h-full rounded-full" style="width:{{ $item['percent'] ?? 0 }}%; background:#00E676"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Mental & taktik --}}
        <div class="lg:col-span-2 rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <h3 class="font-display text-lg tracking-widest flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full" style="background:#00E676"></span> Assessment Mental & Taktik Saya
            </h3>
            @foreach([['🧠 Mental Assessment', $mental, 'player.mental'], ['⚽ Taktik Assessment', $taktik, 'player.taktik']] as [$label, $item, $routeName])
                <a href="{{ route($routeName) }}" class="flex items-center justify-between py-3 hover:opacity-80"
                    style="border-bottom:1px solid rgba(0,230,118,0.08)">
                    <div>
                        <div class="font-semibold text-sm">{{ $label }}</div>
                        <div class="text-xs" style="color:#7A8D9E">
                            @if($item) {{ $item->assessment_date->format('d M Y') }} · {{ $item->user->name ?? '-' }}
                            @else Belum ada assessment @endif
                        </div>
                    </div>
                    @if($item)
                        <span class="px-3 py-1 rounded-full text-sm font-bold" style="background:rgba(0,230,118,0.1); color:#00E676">{{ $item->average_score }}/5</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Menu cepat --}}
        <div class="rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <h3 class="font-display text-lg tracking-widest flex items-center gap-2 mb-4">
                <span class="w-2 h-2 rounded-full" style="background:#00E676"></span> Menu Cepat
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <a href="{{ route('player.tests') }}" class="rounded-lg p-3" style="background:rgba(0,230,118,0.08); border:1px solid rgba(0,230,118,0.25)">
                    <div class="font-semibold" style="color:#00E676">Laporan Fisioterapi</div>
                    <div class="text-xs" style="color:#7A8D9E">Riwayat 10 tes fisik</div>
                </a>
                <a href="{{ route('player.mental') }}" class="rounded-lg p-3" style="background:rgba(179,157,219,0.08); border:1px solid rgba(179,157,219,0.3)">
                    <div class="font-semibold" style="color:#B39DDB">Assessment Mental</div>
                    <div class="text-xs" style="color:#7A8D9E">Detail skor mental</div>
                </a>
                <a href="{{ route('player.profile') }}" class="rounded-lg p-3" style="background:rgba(64,196,255,0.08); border:1px solid rgba(64,196,255,0.3)">
                    <div class="font-semibold" style="color:#40C4FF">Update Profil</div>
                    <div class="text-xs" style="color:#7A8D9E">Data diri & kontak</div>
                </a>
                <a href="{{ route('player.taktik') }}" class="rounded-lg p-3" style="background:rgba(255,171,64,0.08); border:1px solid rgba(255,171,64,0.3)">
                    <div class="font-semibold" style="color:#FFAB40">Assessment Taktik</div>
                    <div class="text-xs" style="color:#7A8D9E">Detail skor taktik</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection