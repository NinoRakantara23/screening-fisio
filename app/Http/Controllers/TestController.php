<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\TestResult;

class TestController extends Controller
{
    /**
     * Aturan validasi kolom identitas & tanda vital — dipakai semua form tes.
     * gender wajib dipilih di form (x-athlete-identity); tinggi, berat & vital opsional.
     */
    private array $identityRules = [
        'gender' => 'required|in:putra,putri',
        'height' => 'nullable|numeric|min:0',
        'weight' => 'nullable|numeric|min:0',
        'vital_td' => 'nullable|string',
        'vital_suhu' => 'nullable|string',
        'vital_nadi' => 'nullable|string',
        'vital_respirasi' => 'nullable|string',
    ];

    /**
     * Semua Tes — index page
     */
    public function index()
    {
        return view('tests.index');
    }

    /**
     * Helper: ambil daftar atlet untuk dropdown form
     */
    private function athletes()
    {
        return Athlete::orderBy('name')->get();
    }

    private function saveTestResult(Request $request, array $validated, string $testType): TestResult
    {
        $athleteId = $request->validate([
            'athlete_id' => 'nullable|exists:athletes,id',
        ])['athlete_id'] ?? null;

        return TestResult::create(array_merge($validated, [
            'athlete_id' => $athleteId,
            'user_id' => auth()->id(),
            'test_type' => $testType,
        ]));
    }

    private function enduranceRating(float $distance): string
    {
        return match (true) {
            $distance > 2400 => 'Elite',
            $distance >= 2000 => 'Excellent',
            $distance >= 1520 => 'Good',
            $distance >= 1040 => 'Average',
            $distance >= 520 => 'Below Average',
            default => 'Very Poor',
        };
    }

    private function speedAssessment(float $seconds, string $gender): array
    {
        $limits = $gender === 'putra'
            ? [6.7, 7.6, 8.7, 10.3]
            : [7.7, 8.7, 9.9, 11.9];
        $score = 1;

        foreach ($limits as $index => $limit) {
            if ($seconds <= $limit) {
                $score = 5 - $index;
                break;
            }
        }

        return [
            'score' => $score,
            'rating' => ['Kurang Sekali', 'Kurang', 'Cukup', 'Baik', 'Baik Sekali'][$score - 1],
        ];
    }

    private function agilityRating(float $seconds, string $gender, bool $illinois = false): string
    {
        if ($illinois) {
            $limits = $gender === 'putra'
                ? [15.2, 16.1, 18.1, 18.2]
                : [17.0, 17.9, 21.7, 23.0];
            $ratings = ['Excellent', 'Above Average', 'Average', 'Below Average', 'Poor'];
        } else {
            $limits = $gender === 'putra'
                ? [11.2, 13.3, 15.5, 17.8]
                : [12.2, 15.3, 18.5, 21.8];
            $ratings = ['Luar Biasa', 'Diatas Rata-rata', 'Rata-rata', 'Di bawah rata-rata', 'Rendah'];
        }

        foreach ($limits as $index => $limit) {
            if ($index === 0 ? $seconds < $limit : $seconds <= $limit) {
                return $ratings[$index];
            }
        }

        return $ratings[4];
    }

    private function strengthAssessment(int $count, string $gender, bool $situps = false): array
    {
        $limits = match ([$gender, $situps]) {
            ['putra', false] => [38, 29, 20, 12],
            ['putri', false] => [21, 16, 10, 5],
            ['putra', true] => [90, 70, 50, 30],
            ['putri', true] => [88, 69, 48, 29],
        };
        $ratings = ['Sempurna', 'Baik Sekali', 'Baik', 'Cukup', 'Kurang'];

        foreach ($limits as $index => $limit) {
            if ($count >= $limit) {
                $score = 5 - $index;
                return ['score' => $score, 'rating' => $ratings[$index]];
            }
        }

        return ['score' => 1, 'rating' => 'Kurang'];
    }

    private function powerRating(float $distance, string $gender): string
    {
        $limits = $gender === 'putra'
            ? [250, 240, 230, 220, 210, 190]
            : [200, 190, 180, 170, 160, 140];
        $ratings = ['Luar Biasa', 'Baik Sekali', 'Di Atas Rata-rata', 'Rata-rata', 'Di Bawah Rata-rata', 'Buruk', 'Sangat Buruk'];

        foreach ($limits as $index => $limit) {
            if (($index === 0 && $distance > $limit) || ($index > 0 && $distance >= $limit + 1)) {
                return $ratings[$index];
            }
        }

        return $ratings[6];
    }

    private function accuracyRating(array $notes): ?string
    {
        if (count($notes) !== 3 || in_array(null, $notes, true)) {
            return null;
        }

        $total = array_sum(array_map(
            fn(string $note) => ['sasaran' => 2, 'tali' => 1, 'gagal' => 0][$note],
            $notes
        ));

        return match (true) {
            $total >= 5 => 'Excellent',
            $total === 4 => 'Good',
            $total === 3 => 'Average',
            $total === 2 => 'Weak',
            default => 'Poor',
        };
    }

    private function flexibilityRating(float $distance, string $gender): string
    {
        $limits = $gender === 'putra'
            ? [35, 32.51, 29.51, 26.50]
            : [32, 30.51, 26.51, 24.25];
        $ratings = ['Excellent', 'Good', 'Average', 'Fair', 'Poor'];

        foreach ($limits as $index => $limit) {
            if (($index === 0 && $distance > $limit) || ($index > 0 && $distance >= $limit)) {
                return $ratings[$index];
            }
        }

        return $ratings[4];
    }

    private function balanceResults(array $validated): array
    {
        $directions = ['anterior' => 'a', 'pm' => 'pm', 'pl' => 'pl'];
        $composites = [];

        foreach ($directions as $direction => $suffix) {
            $left = $validated['left_' . $suffix] ?? null;
            $right = $validated['right_' . $suffix] ?? null;
            $difference = $left !== null && $right !== null ? abs((float) $left - (float) $right) : null;

            $validated['res_left_' . $direction] = $left;
            $validated['res_right_' . $direction] = $right;
            $validated['res_diff_' . $direction] = $difference !== null ? number_format($difference, 1, '.', '') : null;
            $validated['std_' . $direction] = $difference !== null
                ? ($difference < 4 ? 'optimal' : 'below')
                : null;
        }

        $legLength = (float) $validated['leg_length'];
        foreach (['left', 'right'] as $side) {
            $reaches = array_map(fn(string $suffix) => $validated[$side . '_' . $suffix] ?? null, ['a', 'pm', 'pl']);
            $composite = !in_array(null, $reaches, true)
                ? array_sum(array_map('floatval', $reaches)) / (3 * $legLength) * 100
                : null;
            $validated['composite_' . $side] = $composite !== null
                ? number_format($composite, 1, '.', '') . '%'
                : null;
            if ($composite !== null) {
                $composites[] = $composite;
            }
        }

        $validated['std_composite'] = count($composites) === 2
            ? ((array_sum($composites) / 2) > 89 ? 'optimal' : 'below')
            : null;
        $validated['rating'] = $validated['std_composite'];

        return $validated;
    }

    // ================================================================
    //  1. ENDURANCE — YoYo Test
    // ================================================================
    public function form_endurance()
    {
        return view('forms.endurance', ['athletes' => $this->athletes()]);
    }

    public function store_endurance(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'distance' => 'required|numeric|min:0',
            'level' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $validated['rating'] = $this->enduranceRating((float) $validated['distance']);
        $this->saveTestResult($request, $validated, 'endurance');

        return redirect()->route('forms.endurance')
            ->with('success', 'Data tes Yo-Yo berhasil disimpan!');
    }

    // ================================================================
    //  2. SPEED — 50 Meter
    // ================================================================
    public function form_speed()
    {
        return view('forms.speed', ['athletes' => $this->athletes()]);
    }

    public function store_speed(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'time_seconds' => 'required|numeric|min:0',
            'conclusion' => 'nullable|string',
        ]);

        $assessment = $this->speedAssessment((float) $validated['time_seconds'], $validated['gender']);
        $validated['score'] = $assessment['score'];
        $validated['rating'] = $assessment['rating'];
        $this->saveTestResult($request, $validated, 'speed');

        return redirect()->route('forms.speed')
            ->with('success', 'Data tes kecepatan berhasil disimpan!');
    }

    // ================================================================
    //  3. AGILITY — Hexagonal + Illinois
    // ================================================================
    public function form_agility()
    {
        return view('forms.agility', ['athletes' => $this->athletes()]);
    }

    public function store_agility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'hex_time1' => 'nullable|numeric',
            'hex_time2' => 'nullable|numeric',
            'ill_time' => 'nullable|numeric',
            'conclusion' => 'nullable|string',
        ]);

        $hexTimes = [$validated['hex_time1'] ?? null, $validated['hex_time2'] ?? null];
        $validated['hex_avg'] = !in_array(null, $hexTimes, true)
            ? round(array_sum(array_map('floatval', $hexTimes)) / 2, 2)
            : null;
        $validated['hex_rating'] = $validated['hex_avg'] !== null
            ? $this->agilityRating((float) $validated['hex_avg'], $validated['gender'])
            : null;
        $validated['ill_rating'] = isset($validated['ill_time'])
            ? $this->agilityRating((float) $validated['ill_time'], $validated['gender'], true)
            : null;
        $this->saveTestResult($request, $validated, 'agility');

        return redirect()->route('forms.agility')
            ->with('success', 'Data tes kelincahan berhasil disimpan!');
    }

    // ================================================================
    //  4. STRENGTH — Push Up & Sit Up
    // ================================================================
    public function form_strength()
    {
        return view('forms.strength', ['athletes' => $this->athletes()]);
    }

    public function store_strength(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'pushup_count' => 'nullable|integer|min:0',
            'pushup_note' => 'nullable|string',
            'situp_count' => 'nullable|integer|min:0',
            'situp_note' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        foreach (['pushup', 'situp'] as $exercise) {
            $count = $validated[$exercise . '_count'] ?? null;
            $assessment = $count !== null
                ? $this->strengthAssessment((int) $count, $validated['gender'], $exercise === 'situp')
                : ['score' => null, 'rating' => null];
            $validated[$exercise . '_score'] = $assessment['score'];
            $validated[$exercise . '_rating'] = $assessment['rating'];
        }
        $this->saveTestResult($request, $validated, 'strength');

        return redirect()->route('forms.strength')
            ->with('success', 'Data tes kekuatan berhasil disimpan!');
    }

    // ================================================================
    //  5. POWER — Standing Long Jump
    // ================================================================
    public function form_power()
    {
        return view('forms.power', ['athletes' => $this->athletes()]);
    }

    public function store_power(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'try1' => 'nullable|numeric',
            'try2' => 'nullable|numeric',
            'try3' => 'nullable|numeric',
            'best' => 'required|numeric|min:0',
            'conclusion' => 'nullable|string',
        ]);

        $validated['rating'] = $this->powerRating((float) $validated['best'], $validated['gender']);
        $this->saveTestResult($request, $validated, 'power');

        return redirect()->route('forms.power')
            ->with('success', 'Data tes daya ledak berhasil disimpan!');
    }

    // ================================================================
    //  6. ACCURACY — Shooting
    // ================================================================
    public function form_accuracy()
    {
        return view('forms.accuracy', ['athletes' => $this->athletes()]);
    }

    public function store_accuracy(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'shot1_time' => 'nullable|numeric',
            'shot1_note' => 'nullable|in:sasaran,tali,gagal',
            'shot2_time' => 'nullable|numeric',
            'shot2_note' => 'nullable|in:sasaran,tali,gagal',
            'shot3_time' => 'nullable|numeric',
            'shot3_note' => 'nullable|in:sasaran,tali,gagal',
            'conclusion' => 'nullable|string',
        ]);

        $scoreByNote = ['sasaran' => 2, 'tali' => 1, 'gagal' => 0];
        $notes = [];
        foreach ([1, 2, 3] as $attempt) {
            $note = $validated['shot' . $attempt . '_note'] ?? null;
            $notes[] = $note;
            $validated['shot' . $attempt . '_score'] = $note !== null ? $scoreByNote[$note] : null;
        }
        $rating = $this->accuracyRating($notes);
        $validated['total_score'] = $rating !== null
            ? array_sum(array_map(fn($note) => $scoreByNote[$note], $notes))
            : null;
        $validated['rating'] = $rating;
        $this->saveTestResult($request, $validated, 'accuracy');

        return redirect()->route('forms.accuracy')
            ->with('success', 'Data Akurasi Tendangan berhasil disimpan!');
    }

    // ================================================================
    //  7. REACTION — Ruler Drop Test
    // ================================================================
    public function form_reaction()
    {
        return view('forms.reaction', ['athletes' => $this->athletes()]);
    }

    public function store_reaction(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'value_cm' => 'required|numeric|min:0',
            'conclusion' => 'nullable|string',
        ]);

        $validated['rating'] = match (true) {
            $validated['value_cm'] < 7.5 => 'Excellent',
            $validated['value_cm'] <= 15.9 => 'Above Average',
            $validated['value_cm'] <= 20.4 => 'Average',
            $validated['value_cm'] <= 28 => 'Below Average',
            default => 'Poor',
        };
        $this->saveTestResult($request, $validated, 'reaction');

        return redirect()->route('forms.reaction')
            ->with('success', 'Data Kecepatan Reaksi berhasil disimpan!');
    }

    // ================================================================
    //  8. COORDINATION — Fukuda Step Test
    // ================================================================
    public function form_coordination()
    {
        return view('forms.coordination', ['athletes' => $this->athletes()]);
    }

    public function store_coordination(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'steps50' => 'nullable|numeric',
            'steps100' => 'nullable|numeric',
            'rotation_direction' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $validated['steps50_result'] = isset($validated['steps50'])
            ? ((float) $validated['steps50'] < 30 ? 'normal' : 'abnormal')
            : null;
        $validated['steps100_result'] = isset($validated['steps100'])
            ? ((float) $validated['steps100'] < 45 ? 'normal' : 'abnormal')
            : null;
        $this->saveTestResult($request, $validated, 'coordination');

        return redirect()->route('forms.coordination')
            ->with('success', 'Data Koordinasi berhasil disimpan!');
    }

    // ================================================================
    //  9. FLEXIBILITY — Ankle Test
    // ================================================================
    public function form_flexibility()
    {
        return view('forms.flexibility', ['athletes' => $this->athletes()]);
    }

    public function store_flexibility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'try1' => 'nullable|numeric',
            'try2' => 'nullable|numeric',
            'try3' => 'nullable|numeric',
            'best' => 'required|numeric|min:0',
            'conclusion' => 'nullable|string',
        ]);

        $validated['rating'] = $this->flexibilityRating((float) $validated['best'], $validated['gender']);
        $this->saveTestResult($request, $validated, 'flexibility');

        return redirect()->route('forms.flexibility')
            ->with('success', 'Data Fleksibilitas Ankle berhasil disimpan!');
    }

    // ================================================================
    //  10. BALANCE — Y Balance Test
    // ================================================================
    public function form_balance()
    {
        return view('forms.balance', ['athletes' => $this->athletes()]);
    }

    public function store_balance(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            ...$this->identityRules,
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'leg_length' => 'required|numeric|min:1',
            'left_a' => 'nullable|numeric',
            'left_pm' => 'nullable|numeric',
            'left_pl' => 'nullable|numeric',
            'right_a' => 'nullable|numeric',
            'right_pm' => 'nullable|numeric',
            'right_pl' => 'nullable|numeric',
            'conclusion' => 'nullable|string',
        ]);

        $validated = $this->balanceResults($validated);
        $this->saveTestResult($request, $validated, 'balance');

        return redirect()->route('forms.balance')
            ->with('success', 'Data tes keseimbangan Y berhasil disimpan!');
    }
}
