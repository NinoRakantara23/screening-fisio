<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use App\Models\TestResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PlayerController extends Controller
{
    /** Rating -> persen. Sesuaikan dengan nilai rating yang dipakai di form tes Anda. */
    private const RATING_PERCENT = [
        'excellent' => 100, 'very good' => 90, 'good' => 80, 'normal' => 75,
        'average' => 60, 'fair' => 55, 'below average' => 40, 'poor' => 30,
    ];

    /* ---------- Helper ---------- */

    private function athlete(): Athlete
    {
        $athlete = auth()->user()->athlete;
        abort_if(!$athlete, 403, 'Akun ini belum terhubung dengan data atlet.');
        return $athlete;
    }

    /** Batasi query ke atlet ini (termasuk data lama yang hanya terhubung lewat nama). */
    private function scopeAthlete($query, Athlete $athlete)
    {
        return $query->where(function ($q) use ($athlete) {
            $q->where('athlete_id', $athlete->id)
              ->orWhere(fn($legacy) => $legacy->whereNull('athlete_id')->where('name', $athlete->name));
        });
    }

    private function allResults(Athlete $athlete): Collection
    {
        return $this->scopeAthlete(TestResult::query(), $athlete)->latest('test_date')->get();
    }

    private function latestByType(Collection $results): Collection
    {
        return $results->groupBy(fn($r) => mb_strtolower($r->test_type))->map->first();
    }

    private function ratingOf(TestResult $result): ?string
    {
        $fields = match (mb_strtolower($result->test_type)) {
            'agility'  => ['hex_rating', 'ill_rating', 'rating'],
            'strength' => ['pushup_rating', 'situp_rating', 'rating'],
            default    => ['rating'],
        };
        foreach ($fields as $field) {
            if ($result->{$field}) {
                return $result->{$field};
            }
        }
        return null;
    }

    private function percentOf(TestResult $result): ?int
    {
        $rating = $this->ratingOf($result);
        return $rating ? (self::RATING_PERCENT[mb_strtolower(trim($rating))] ?? null) : null;
    }

    private function gradeFor(?float $percent): string
    {
        return match (true) {
            $percent === null => '—',
            $percent >= 90 => 'A',
            $percent >= 85 => 'A-',
            $percent >= 80 => 'B+',
            $percent >= 75 => 'B',
            $percent >= 70 => 'B-',
            $percent >= 65 => 'C+',
            $percent >= 60 => 'C',
            default        => 'D',
        };
    }

    private function overall(Collection $latest): array
    {
        $percents = $latest->map(fn($r) => $this->percentOf($r))->filter(fn($p) => $p !== null);
        return [$percents, $this->gradeFor($percents->isEmpty() ? null : $percents->avg())];
    }

    /* ---------- Halaman ---------- */

    public function dashboard()
    {
        $athlete = $this->athlete();
        $all = $this->allResults($athlete);
        $latest = $this->latestByType($all);
        [$percents, $grade] = $this->overall($latest);

        $ofType = fn(string $type) => $all->filter(fn($r) => mb_strtolower($r->test_type) === $type);

        $yoyo  = $ofType('endurance')->whereNotNull('distance')->max('distance');
        $speed = $ofType('speed')->whereNotNull('time_seconds')->min('time_seconds');
        $agi   = $latest->get('agility')?->hex_avg;

        $mental = $this->scopeAthlete(MentalAssessment::query(), $athlete)->latest('assessment_date')->first();
        $taktik = $this->scopeAthlete(TaktikAssessment::query(), $athlete)->latest('assessment_date')->first();

        $stats = [
            ['value' => $yoyo  !== null ? number_format($yoyo, 0) . 'm' : '—', 'label' => 'YoYo Terbaik', 'color' => '#00E676'],
            ['value' => $speed !== null ? number_format($speed, 1) . 's' : '—', 'label' => 'Speed 50M',   'color' => '#FFD740'],
            ['value' => $agi   !== null ? number_format($agi, 1) . 's'   : '—', 'label' => 'Agility',     'color' => '#40C4FF'],
            ['value' => $mental ? $mental->average_score . '/5' : '—',           'label' => 'Skor Mental', 'color' => '#B39DDB'],
            ['value' => $taktik ? $taktik->average_score . '/5' : '—',           'label' => 'Skor Taktik', 'color' => '#FFAB40'],
        ];

        $progress = collect([
            'endurance' => 'Endurance (YoYo)', 'speed' => 'Kecepatan (Speed)',
            'agility' => 'Kelincahan (Agility)', 'strength' => 'Kekuatan (Strength)',
            'power' => 'Power (Jump)',
        ])->map(fn($label, $type) => ['label' => $label, 'percent' => $percents->get($type)]);

        return view('player.dashboard', [
            'athlete'     => $athlete,
            'firstName'   => Str::before($athlete->name, ' '),
            'grade'       => $grade,
            'lastTest'    => $all->first()?->test_date,
            'stats'       => $stats,
            'progress'    => $progress,
            'recentTests' => $all->take(5),
            'mental'      => $mental,
            'taktik'      => $taktik,
        ]);
    }

    public function tests()
    {
        $athlete = $this->athlete();
        $latest = $this->latestByType($this->allResults($athlete));
        [, $grade] = $this->overall($latest);

        $groups = [
            'Endurance & Kecepatan'        => ['endurance', 'speed', 'agility'],
            'Kekuatan & Power'             => ['strength', 'power'],
            'Skill, Reaksi & Koordinasi'   => ['accuracy', 'reaction', 'coordination'],
            'Fleksibilitas & Keseimbangan' => ['flexibility', 'balance'],
        ];
        $labels = collect($groups)->flatten()->mapWithKeys(
            fn($type) => [$type => (new TestResult(['test_type' => $type]))->test_type_label]
        );

        return view('player.tests', compact('athlete', 'latest', 'grade', 'groups', 'labels'));
    }

    public function mental(Request $request)
    {
        return $this->assessmentPage(MentalAssessment::class, 'mental', 'Assessment Mental', $request);
    }

    public function taktik(Request $request)
    {
        return $this->assessmentPage(TaktikAssessment::class, 'taktik', 'Assessment Taktik', $request);
    }

    private function assessmentPage(string $model, string $type, string $title, Request $request)
    {
        $athlete = $this->athlete();

        // Query dibatasi ke atlet ini, jadi ?id= milik atlet lain otomatis 404.
        $history = $this->scopeAthlete($model::query(), $athlete)->latest('assessment_date')->get();
        $assessment = $request->filled('id')
            ? $history->firstWhere('id', (int) $request->query('id'))
            : $history->first();
        abort_if($request->filled('id') && !$assessment, 404);

        return view('player.assessment', [
            'athlete'     => $athlete,
            'type'        => $type,
            'title'       => $title,
            'assessment'  => $assessment,
            'history'     => $history,
            'categories'  => $model::getCategories(),
            'scoreLabels' => $model::getScoreLabels(),
        ]);
    }

    public function profile()
    {
        return view('player.profile', ['athlete' => $this->athlete()]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'phone'  => ['nullable', 'string', 'max:20'],
            'height' => ['nullable', 'numeric', 'between:100,250'],
            'weight' => ['nullable', 'numeric', 'between:30,200'],
        ]);

        $this->athlete()->update($data); // hanya field di atas, nama/posisi tidak bisa diubah

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function pdf()
    {
        $athlete = $this->athlete();
        $results = $this->allResults($athlete);

        if ($results->isEmpty()) {
            return back()->with('error', 'Belum ada data tes untuk diunduh.');
        }

        return Pdf::loadView('report.pdf', [
            'profileResult' => $results->first(),
            'testResults'   => $results,
            'printedBy'     => auth()->user()->name,
        ])->setPaper('a4', 'portrait')
          ->download('laporan-screening-' . Str::slug($athlete->name) . '.pdf');
    }
}