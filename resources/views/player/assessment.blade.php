@extends('layouts.app')
@section('title', $title)
@section('page-title', $title)

@section('content')
<div class="max-w-4xl mx-auto">
    @if(!$assessment)
        <div class="rounded-xl p-10 text-center" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15); color:#7A8D9E">
            Belum ada {{ strtolower($title) }} untuk Anda.
        </div>
    @else
        {{-- Header --}}
        <div class="rounded-xl p-7 mb-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold mb-1" style="color:#00E676">{{ $athlete->name }}</h2>
                    <p class="text-sm" style="color:#7A8D9E">Assessment ID: #{{ $assessment->id }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" style="color:#00E676">{{ $assessment->average_score }}/5</div>
                    <div class="text-xs" style="color:#7A8D9E">Rata-rata Skor</div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs" style="color:#7A8D9E">Tanggal Assessment</div>
                    <div class="font-semibold">{{ $assessment->assessment_date->format('d M Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-xs" style="color:#7A8D9E">Penilai</div>
                    <div class="font-semibold">{{ $assessment->user->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs" style="color:#7A8D9E">Dibuat</div>
                    <div class="font-semibold">{{ $assessment->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Skor --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            @foreach($categories as $key => $label)
                @php
                    $score = $assessment->$key;
                    $note = $assessment->{$key . '_note'};
                @endphp
                <div class="rounded-lg p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">{{ $label }}</h3>
                        @if($score)
                            <span class="px-3 py-1 rounded-full text-sm font-bold" style="background:rgba(0,230,118,0.1); color:#00E676">
                                {{ $score }}/5 · {{ $scoreLabels[$key][$score] ?? '' }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs" style="background:rgba(128,128,128,0.1); color:#7A8D9E">Tidak dinilai</span>
                        @endif
                    </div>
                    @if($score)
                        <div class="w-full h-2 rounded-full mb-3" style="background:rgba(0,230,118,0.1)">
                            <div class="h-full rounded-full" style="width:{{ ($score / 5) * 100 }}%; background:#00E676"></div>
                        </div>
                    @endif
                    <p class="text-sm {{ $note ? '' : 'italic' }}" style="color:#7A8D9E">{{ $note ?: 'Tidak ada catatan' }}</p>
                </div>
            @endforeach
        </div>

        {{-- Penilaian, rekomendasi, formasi --}}
        <div class="grid grid-cols-1 gap-5 mb-6">
            @if($assessment->overall_assessment)
                <div class="rounded-lg p-5" style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)">
                    <h3 class="font-semibold mb-3" style="color:#00E676">📋 Penilaian Keseluruhan</h3>
                    <p class="text-sm leading-relaxed">{{ $assessment->overall_assessment }}</p>
                </div>
            @endif
            @if($assessment->recommendations)
                <div class="rounded-lg p-5" style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)">
                    <h3 class="font-semibold mb-3" style="color:#00E676">💡 Rekomendasi</h3>
                    <p class="text-sm leading-relaxed">{{ $assessment->recommendations }}</p>
                </div>
            @endif
            @if($type === 'taktik' && $assessment->formation_notes)
                <div class="rounded-lg p-5" style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)">
                    <h3 class="font-semibold mb-3" style="color:#00E676">🎯 Catatan Formasi & Taktik Khusus</h3>
                    <p class="text-sm leading-relaxed">{{ $assessment->formation_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Riwayat --}}
        @if($history->count() > 1)
            <div class="rounded-lg p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                <h3 class="font-semibold mb-3">Riwayat Assessment</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($history as $item)
                        <a href="{{ route('player.' . $type, ['id' => $item->id]) }}"
                            class="px-3 py-1 rounded-lg text-sm"
                            style="border:1px solid rgba(0,230,118,{{ $item->id === $assessment->id ? '0.6' : '0.2' }}); color:{{ $item->id === $assessment->id ? '#00E676' : '#7A8D9E' }}">
                            {{ $item->assessment_date->format('d M Y') }} · {{ $item->average_score }}/5
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
@endsection