<?php
// app/Http/Controllers/ReportController.php
namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\TestResult;
use App\Exports\ScreeningExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$athletes, $testResults, $position, $date] = $this->filteredReportData($request);

        $resultsByAthleteId = $testResults->filter(fn($result) => $result->athlete_id)
            ->groupBy('athlete_id');
        $resultsByLegacyName = $testResults->filter(fn($result) => !$result->athlete_id)
            ->groupBy(fn($result) => mb_strtolower(trim($result->name)));

        $reportResults = collect();
        $reports = $athletes->map(function ($athlete) use ($resultsByAthleteId, $resultsByLegacyName, &$reportResults) {
            $matchedResults = collect($resultsByAthleteId->get($athlete->id, []))
                ->concat($resultsByLegacyName->get(mb_strtolower(trim($athlete->name)), collect()))
                ->sortByDesc('test_date');
            $latestResults = $matchedResults
                ->groupBy(fn($result) => mb_strtolower($result->test_type))
                ->map(fn($results) => $results->first());

            $reportResults = $reportResults->merge($latestResults->values());
            $ratingFor = function (string $type, array $fields = ['rating']) use ($latestResults) {
                $result = $latestResults->get($type);
                if (!$result) {
                    return null;
                }

                foreach ($fields as $field) {
                    if ($result->{$field}) {
                        return $result->{$field};
                    }
                }

                return 'Tersimpan';
            };

            return (object) [
                'athlete_id' => $athlete->id,
                'athlete_name' => $athlete->name,
                'position' => $athlete->display_position,
                'yoyo_rating' => $ratingFor('endurance'),
                'speed_rating' => $ratingFor('speed'),
                'agility_rating' => $ratingFor('agility', ['hex_rating', 'ill_rating', 'rating']),
                'strength_rating' => $ratingFor('strength', ['pushup_rating', 'situp_rating', 'rating']),
                'power_rating' => $ratingFor('power'),
                'accuracy_rating' => $ratingFor('accuracy'),
                'reaction_rating' => $ratingFor('reaction'),
                'coordination_rating' => $ratingFor('coordination'),
                'flexibility_rating' => $ratingFor('flexibility'),
                'balance_rating' => $ratingFor('balance'),
                'test_count' => $latestResults->count(),
            ];
        })->values();

        $average = fn($field, $suffix, $decimals = 1) => $reportResults->pluck($field)
            ->filter(fn($value) => is_numeric($value))
            ->isNotEmpty()
            ? number_format((float) $reportResults->pluck($field)->filter(fn($value) => is_numeric($value))->avg(), $decimals) . $suffix
            : '—';

        $summaryStats = [
            ['value' => $average('distance', 'm', 0), 'label' => 'Rata-rata Yo-Yo', 'color' => '#00E676'],
            ['value' => $average('time_seconds', 's', 2), 'label' => 'Rata-rata kecepatan', 'color' => '#FFD600'],
            ['value' => $average('hex_avg', 's', 2), 'label' => 'Rata-rata kelincahan', 'color' => '#4FC3F7'],
            ['value' => $average('best', 'cm', 1), 'label' => 'Rata-rata lompatan', 'color' => '#FF9800'],
            ['value' => $average('value_cm', 'cm', 1), 'label' => 'Rata-rata reaksi', 'color' => '#FF4444'],
        ];

        return view('report.index', compact('reports', 'position', 'date', 'summaryStats'));
    }

    private function filteredReportData(Request $request): array
    {
        $filters = $request->validate([
            'position' => ['nullable', 'string', 'in:' . implode(',', Athlete::POSITIONS)],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $position = $filters['position'] ?? null;
        $date = $filters['date'] ?? null;

        $athletesQuery = Athlete::query();
        if ($position) {
            $positionAliases = match ($position) {
                'Penyerang' => ['Penyerang', 'Striker', 'Forward', 'Winger', 'Sayap'],
                'Gelandang' => ['Gelandang', 'Playmaker', 'Midfielder'],
                'Bek' => ['Bek', 'Defender'],
                default => [$position],
            };
            $athletesQuery->whereIn('position', $positionAliases);
        }
        $athletes = $athletesQuery->orderBy('name')->get();

        $athleteIds = $athletes->pluck('id');
        $athleteNames = $athletes->pluck('name');
        $testResultsQuery = TestResult::query()
            ->where(function ($query) use ($athleteIds, $athleteNames) {
                $query->whereIn('athlete_id', $athleteIds)
                    ->orWhere(function ($legacyQuery) use ($athleteNames) {
                        $legacyQuery->whereNull('athlete_id')
                            ->whereIn('name', $athleteNames);
                    });
            })
            ->latest('test_date');

        if ($date) {
            $testResultsQuery->whereDate('test_date', $date);
        }

        return [$athletes, $testResultsQuery->get(), $position, $date];
    }

    public function exportPdf(Request $request)
    {
        // Get athlete name from query parameter
        $athleteName = $request->query('athlete');

        if ($athleteName) {
            // Export PDF for specific athlete
            return $this->exportSingleAthletePdf($athleteName);
        } else {
            [, $testResults] = $this->filteredReportData($request);
            return $this->exportAllAthletesPdf($testResults);
        }
    }

    private function exportSingleAthletePdf($athleteName)
    {
        $results = TestResult::where('name', $athleteName)
            ->latest('test_date')
            ->get();

        // Check if athlete has any test results
        if ($results->isEmpty()) {
            return redirect()->back()->with('error', 'Data atlet tidak ditemukan');
        }

        // Get athlete data from first result (assuming all results have same athlete data)
        $athleteData = $results->first();

        // Prepare data for PDF view
        $data = [
            'profileResult' => $athleteData,
            'testResults' => $results,
            'printedBy' => auth()->user()->name ?? 'Fisioterapis',
        ];

        // Generate PDF
        $pdf = Pdf::loadView('report.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("laporan-screening-{$athleteName}.pdf");
    }

    private function exportAllAthletesPdf(Collection $allResults)
    {
        $athletesData = [];

        // Group by athlete name
        foreach ($allResults->groupBy('name') as $athleteName => $results) {
            $athleteData = $results->first(); // Athlete info
            $athletesData[] = [
                'profileResult' => $athleteData,
                'testResults' => $results,
            ];
        }

        if (empty($athletesData)) {
            return redirect()->back()->with('error', 'Tidak ada data atlet untuk diexport');
        }

        // Generate PDF with all athletes
        $pdf = Pdf::loadView('report.all_pdf', [
            'athletes' => $athletesData,
            'printedBy' => auth()->user()->name ?? 'Fisioterapis',
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->download("laporan-screening-semua-atlet.pdf");
    }

    public function exportExcel(Request $request)
    {
        [, $results] = $this->filteredReportData($request);
        $xlsxPath = app(ScreeningExcelExport::class)->create($results);
        $filename = 'laporan-screening-' . now()->format('Y-m-d') . '.xlsx';

        return response()->download(
            $xlsxPath,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function athleteReport($id)
    {
        $athlete = Athlete::findOrFail($id);
        $results = TestResult::where(function ($query) use ($athlete) {
            $query->where('athlete_id', $athlete->id)
                ->orWhere(function ($legacyQuery) use ($athlete) {
                    $legacyQuery->whereNull('athlete_id')
                        ->where('name', $athlete->name);
                });
        })->latest('test_date')->get();

        return view('report.athlete', [
            'athlete' => $athlete,
            'results' => $results,
        ]);
    }
}

