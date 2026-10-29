@extends('layouts.app')
@section('title', 'Hasil Fisioterapi')
@section('page-title', 'Hasil Fisioterapi')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="rounded-xl p-6 mb-6 flex items-center justify-between" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <div>
            <h2 class="text-2xl font-bold" style="color:#00E676">{{ $athlete->name }}</h2>
            <p class="text-sm" style="color:#7A8D9E">
                Rekap Tes Fisik · Posisi {{ $athlete->position ?? '-' }} · Usia {{ $athlete->age ?? '-' }} Tahun
            </p>
        </div>
        <div class="flex items-center gap-5">
            <a href="{{ route('player.pdf') }}" class="px-4 py-2 rounded-lg text-sm font-semibold"
                style="background:#00E676; color:#080C10">📄 Download PDF</a>
            <div class="text-right">
                <div class="text-4xl font-bold" style="color:#00E676">{{ $grade }}</div>
                <div class="text-xs" style="color:#7A8D9E">Grade Keseluruhan</div>
            </div>
        </div>
    </div>

    @foreach($groups as $groupName => $types)
        <p class="text-xs uppercase tracking-widest font-mono2 mb-3 mt-6" style="color:#7A8D9E">{{ $groupName }}</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($types as $type)
                @php $result = $latest->get($type); @endphp
                <div class="rounded-xl p-4" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                    <div class="flex justify-between items-start mb-3 gap-2">
                        <h3 class="font-semibold text-sm">{{ $labels[$type] }}</h3>
                        @if($result)
                            <div class="text-right">
                                @foreach($result->display_ratings as $rating)
                                    <x-rating-badge :rating="$rating" />
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($result)
                        @foreach($result->display_results as $label => $value)
                            @continue(str_contains($label, 'rating') || $label === 'Rating')
                            <div class="flex justify-between text-sm py-1">
                                <span style="color:#7A8D9E">{{ $label }}</span>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endforeach
                        @if($result->conclusion)
                            <p class="text-xs italic mt-2" style="color:#7A8D9E">{{ $result->conclusion }}</p>
                        @endif
                        <p class="text-xs mt-2" style="color:#7A8D9E">{{ $result->test_date?->format('d M Y') }}</p>
                    @else
                        <p class="text-sm italic" style="color:#7A8D9E">Belum dites</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection