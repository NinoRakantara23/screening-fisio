<?php
// app/Models/TestResult.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'athlete_id',
        'test_type',
        'name',
        'age',
        'gender',
        'position',
        'height',
        'weight',
        'phone',
        'test_date',
        'vital_td',
        'vital_suhu',
        'vital_nadi',
        'vital_respirasi',
        'complaint',
        // Endurance
        'distance',
        'level',
        // Speed
        'time_seconds',
        'score',
        // Agility
        'hex_time1',
        'hex_time2',
        'hex_avg',
        'hex_rating',
        'ill_time',
        'ill_rating',
        // Strength
        'pushup_count',
        'pushup_score',
        'pushup_rating',
        'pushup_note',
        'situp_count',
        'situp_score',
        'situp_rating',
        'situp_note',
        // Power & Flexibility (share columns)
        'try1',
        'try2',
        'try3',
        'best',
        // Accuracy
        'shot1_score',
        'shot1_time',
        'shot1_note',
        'shot2_score',
        'shot2_time',
        'shot2_note',
        'shot3_score',
        'shot3_time',
        'shot3_note',
        'total_score',
        // Reaction
        'value_cm',
        // Coordination
        'steps50',
        'steps50_result',
        'steps100',
        'steps100_result',
        'rotation_direction',
        // Balance
        'leg_length',
        'left_a',
        'left_pm',
        'left_pl',
        'right_a',
        'right_pm',
        'right_pl',
        'res_left_anterior',
        'res_right_anterior',
        'res_diff_anterior',
        'res_left_pm',
        'res_right_pm',
        'res_diff_pm',
        'res_left_pl',
        'res_right_pl',
        'res_diff_pl',
        'composite_left',
        'composite_right',
        'std_anterior',
        'std_pm',
        'std_pl',
        'std_composite',
        // Shared
        'rating',
        'conclusion',
    ];

    protected $casts = ['test_date' => 'datetime'];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter by test type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('test_type', $type);
    }

    public function getTestTypeLabelAttribute(): string
    {
        return [
            'endurance' => 'Daya Tahan Yo-Yo',
            'speed' => 'Lari Sprint 50 Meter',
            'agility' => 'Kelincahan',
            'strength' => 'Kekuatan Otot',
            'power' => 'Daya Ledak (Lompat Jauh)',
            'accuracy' => 'Akurasi Tendangan',
            'reaction' => 'Kecepatan Reaksi',
            'coordination' => 'Koordinasi Gerak',
            'flexibility' => 'Kelenturan Pergelangan Kaki',
            'balance' => 'Keseimbangan Y',
        ][$this->test_type] ?? ucfirst((string) $this->test_type);
    }

    public function getDisplayResultsAttribute(): array
    {
        $values = match ($this->test_type) {
            'endurance' => ['Jarak tempuh' => $this->distance !== null ? $this->distance . ' m' : null, 'Tingkatan' => $this->level],
            'speed' => ['Waktu' => $this->time_seconds !== null ? $this->time_seconds . ' detik' : null, 'Skor' => $this->score !== null ? $this->score . '/5' : null, 'Kriteria' => $this->ratingLabel($this->rating)],
            'agility' => ['Rata-rata Hexagonal' => $this->hex_avg !== null ? $this->hex_avg . ' detik' : null, 'Waktu Illinois' => $this->ill_time !== null ? $this->ill_time . ' detik' : null],
            'strength' => ['Tekan tubuh' => $this->pushup_count !== null ? $this->pushup_count . ' kali' : null, 'Duduk-baring' => $this->situp_count !== null ? $this->situp_count . ' kali' : null],
            'power', 'flexibility' => ['Hasil terbaik' => $this->best !== null ? $this->best . ' cm' : null],
            'accuracy' => ['Total poin' => $this->total_score],
            'reaction' => ['Jarak jatuh penggaris' => $this->value_cm !== null ? $this->value_cm . ' cm' : null],
            'coordination' => ['50 langkah' => $this->steps50 !== null ? $this->steps50 . '°' : null, '100 langkah' => $this->steps100 !== null ? $this->steps100 . '°' : null, 'Arah putaran' => $this->rotation_direction],
            'balance' => ['Skor gabungan kiri' => $this->composite_left, 'Skor gabungan kanan' => $this->composite_right],
            default => [],
        };

        // Rating tampil untuk semua tipe tes yang menyimpan rating
        if ($this->test_type !== 'speed') { // speed sudah menampilkan rating lewat 'Kriteria'
            foreach ($this->display_ratings as $label => $nilai) {
                $values[$label === 'Rating' ? 'Rating' : $label . ' (rating)'] = $nilai;
            }
        }

        return array_filter($values, fn($value) => $value !== null && $value !== '');
    }

    public function getDisplayRatingsAttribute(): array
    {
        $ratings = match ($this->test_type) {
            'agility' => ['Tes Hexagonal' => $this->hex_rating, 'Tes Illinois' => $this->ill_rating],
            'strength' => ['Tekan tubuh' => $this->pushup_rating, 'Duduk-baring' => $this->situp_rating],
            'coordination' => ['50 langkah' => $this->steps50_result, '100 langkah' => $this->steps100_result],
            'balance' => ['Keseimbangan' => $this->std_composite],
            default => ['Rating' => $this->rating],
        };

        return array_filter($ratings, fn($rating) => $rating !== null && $rating !== '');
    }

    private function ratingLabel(?string $rating): ?string
    {
        if ($rating === null) {
            return null;
        }

        return [
            'elite' => 'Elit',
            'excellent' => 'Sangat Baik',
            'very good' => 'Sangat Baik',
            'good' => 'Baik',
            'above average' => 'Di Atas Rata-rata',
            'average' => 'Rata-rata',
            'fair' => 'Cukup',
            'below average' => 'Di Bawah Rata-rata',
            'below' => 'Di Bawah Rata-rata',
            'weak' => 'Lemah',
            'poor' => 'Buruk',
            'very poor' => 'Sangat Buruk',
            'normal' => 'Normal',
            'abnormal' => 'Tidak Normal',
            'optimal' => 'Optimal',
        ][mb_strtolower(trim($rating))] ?? $rating;
    }
}
