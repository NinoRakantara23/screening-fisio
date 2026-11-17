<?php
// app/Http/Controllers/AthleteController.php
namespace App\Http\Controllers;
use App\Models\Athlete;
use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AthleteController extends Controller
{
    private function normalizedPhone(?string $phone, ?User $player = null): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $phone = User::normalizePhone($phone);
        Validator::make(
            ['phone' => $phone],
            [
                'phone' => [
                    'required',
                    'regex:/^0[0-9]{8,14}$/',
                    Rule::unique('users', 'phone')->ignore($player?->id),
                ],
            ],
            [
                'phone.regex' => 'Nomor HP harus berupa nomor Indonesia yang valid.',
                'phone.unique' => 'Nomor HP tersebut sudah digunakan akun lain.',
            ]
        )->validate();

        return $phone;
    }

    private function syncPlayerAccount(Athlete $athlete): void
    {
        if (!$athlete->phone) {
            return;
        }

        $player = User::firstOrNew(['athlete_id' => $athlete->id]);
        $isNewPlayer = !$player->exists;
        $player->fill([
            'name' => $athlete->name,
            'phone' => $athlete->phone,
            'role' => 'pemain',
        ]);

        if ($isNewPlayer) {
            $player->email = $athlete->phone . '@pemain.footscreen.com';
            $player->password = $athlete->phone;
        }

        $player->save();
    }

    public function index()
    {
        $athletes = Athlete::orderBy('name')->paginate(15);
        return view('athletes.index', compact('athletes'));
    }

    public function edit(Athlete $athlete)
    {
        return view('athletes.edit', compact('athlete'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100',
                'birth_place' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date|before_or_equal:today',
                'age' => 'nullable|integer',
                'gender' => 'nullable|string',
                'position' => 'nullable|in:' . implode(',', Athlete::POSITIONS),
                'height' => 'nullable|numeric',
                'weight' => 'nullable|numeric',
                'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s().-]+$/'],
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'Nama lengkap wajib diisi.',
                'name.max' => 'Nama lengkap maksimal 100 karakter.',
                'birth_place.max' => 'Tempat lahir maksimal 100 karakter.',
                'birth_date.date' => 'Format tanggal lahir tidak valid.',
                'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melewati hari ini.',
                'age.integer' => 'Usia harus berupa angka bulat.',
                'height.numeric' => 'Tinggi badan harus berupa angka.',
                'weight.numeric' => 'Berat badan harus berupa angka.',
                'phone.regex' => 'Format nomor HP tidak valid.',
                'photo.image' => 'Foto harus berupa file gambar.',
                'photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau GIF.',
                'photo.max' => 'Ukuran foto maksimal 2 MB.',
            ]);

            $data['phone'] = $this->normalizedPhone($data['phone'] ?? null);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = Str::uuid() . '.' . $photo->extension();
                $photo->move(public_path('uploads/athletes'), $photoName);
                $data['photo'] = 'uploads/athletes/' . $photoName;
            }

            $athlete = DB::transaction(function () use ($data) {
                $athlete = Athlete::create($data);
                $this->syncPlayerAccount($athlete);

                return $athlete;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'athlete' => $athlete,
                    'player_account_created' => (bool) $athlete->phone,
                ]);
            }

            $message = 'Atlet berhasil ditambahkan!';
            if ($athlete->phone) {
                $message .= ' Akun pemain dibuat; kata sandi awal sama dengan nomor HP.';
            }

            return redirect()->route('athletes.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }
    }
    public function show($id)
    {
        $athlete = Athlete::findOrFail($id);
        $matchAthlete = function ($query) use ($athlete) {
            $query->where('athlete_id', $athlete->id)
                ->orWhere(function ($legacyQuery) use ($athlete) {
                    $legacyQuery->whereNull('athlete_id')
                        ->where('name', $athlete->name);
                });
        };
        $testResults = TestResult::where($matchAthlete)->latest('test_date')->get();
        $mentalAssessments = MentalAssessment::where($matchAthlete)->latest('assessment_date')->get();
        $taktikAssessments = TaktikAssessment::where($matchAthlete)->latest('assessment_date')->get();

        $latestByType = $testResults->groupBy('test_type')->map(fn($results) => $results->first());
        $ratingScore = function ($rating): ?float {
            if (!$rating) {
                return null;
            }

            $rating = mb_strtolower(trim($rating));
            return match (true) {
                str_contains($rating, 'elite'),
                str_contains($rating, 'excellent'),
                str_contains($rating, 'luar biasa'),
                str_contains($rating, 'baik sekali'),
                str_contains($rating, 'sempurna'),
                str_contains($rating, 'optimal') => 5.0,
                str_contains($rating, 'good'),
                str_contains($rating, 'above average'),
                str_contains($rating, 'diatas rata'),
                $rating === 'baik' => 4.0,
                str_contains($rating, 'kurang sekali') => 1.0, // terendah, dicek sebelum 'kurang'
                str_contains($rating, 'below'),
                str_contains($rating, 'di bawah'),
                str_contains($rating, 'dibawah'),
                str_contains($rating, 'kurang'),
                str_contains($rating, 'weak'),
                str_contains($rating, 'fair') => 2.0, // Fair di bawah Average pada form Fleksibilitas
                str_contains($rating, 'average'),
                str_contains($rating, 'rata-rata'),
                str_contains($rating, 'rata rata'),
                str_contains($rating, 'cukup') => 3.0,
                str_contains($rating, 'poor'),
                str_contains($rating, 'miskin'),
                str_contains($rating, 'rendah'),
                str_contains($rating, 'abnormal') => 1.0,
                default => null,
            };
        };
        $averageScore = function (array $scores): ?float {
            $scores = array_values(array_filter($scores, fn($score) => $score !== null));
            return count($scores) ? round(array_sum($scores) / count($scores), 1) : null;
        };
        $coordination = $latestByType->get('coordination');
        $balance = $latestByType->get('balance');
        $coordinationScores = collect([$coordination?->steps50_result, $coordination?->steps100_result])
            ->map(fn($result) => match (mb_strtolower((string) $result)) {
                'normal' => 4.0,
                'abnormal' => 2.0,
                default => null,
            })->all();
        $radarDefinitions = [
            'endurance' => ['label' => 'YoYo', 'color' => '#00E676', 'score' => $ratingScore($latestByType->get('endurance')?->rating)],
            'speed' => ['label' => 'Lari 50 meter', 'color' => '#FFD740', 'score' => $ratingScore($latestByType->get('speed')?->rating)],
            'agility' => [
                'label' => 'Kelincahan',
                'color' => '#40C4FF',
                'score' => $averageScore([
                    $ratingScore($latestByType->get('agility')?->hex_rating),
                    $ratingScore($latestByType->get('agility')?->ill_rating),
                    $ratingScore($latestByType->get('agility')?->rating),
                ])
            ],
            'strength' => [
                'label' => 'Kekuatan',
                'color' => '#FF6E6E',
                'score' => $averageScore([
                    $ratingScore($latestByType->get('strength')?->pushup_rating),
                    $ratingScore($latestByType->get('strength')?->situp_rating),
                    $ratingScore($latestByType->get('strength')?->rating),
                ])
            ],
            'power' => ['label' => 'Daya ledak', 'color' => '#FFAB40', 'score' => $ratingScore($latestByType->get('power')?->rating)],
            'accuracy' => ['label' => 'Akurasi', 'color' => '#26C6DA', 'score' => $ratingScore($latestByType->get('accuracy')?->rating)],
            'reaction' => ['label' => 'Reaksi', 'color' => '#F06292', 'score' => $ratingScore($latestByType->get('reaction')?->rating)],
            'coordination' => ['label' => 'Koordinasi', 'color' => '#B39DDB', 'score' => $averageScore($coordinationScores)],
            'flexibility' => ['label' => 'Fleksibilitas', 'color' => '#AED581', 'score' => $ratingScore($latestByType->get('flexibility')?->rating)],
            'balance' => ['label' => 'Keseimbangan', 'color' => '#FF8A65', 'score' => $ratingScore($balance?->std_composite ?? $balance?->rating)],
        ];
        $radarStats = collect($radarDefinitions)->values();
        $ratedTestCount = $radarStats->whereNotNull('score')->count();

        $testGroups = [
            'Daya tahan' => ['endurance'],
            'Kecepatan' => ['speed', 'reaction'],
            'Kelincahan' => ['agility'],
            'Kekuatan' => ['strength'],
            'Daya ledak' => ['power'],
            'Fungsional' => ['accuracy', 'coordination', 'flexibility', 'balance'],
        ];
        $completedTypes = $testResults->pluck('test_type')->unique();
        $screeningStats = collect($testGroups)->map(function ($types, $label) use ($completedTypes) {
            $completed = $completedTypes->intersect($types)->count();

            return [
                'label' => $label,
                'completed' => $completed,
                'total' => count($types),
                'percentage' => (int) round(($completed / count($types)) * 100),
            ];
        })->values();
        $completedTestTypes = $completedTypes->count();
        $latestTestDate = $testResults->first()?->test_date;

        // Return JSON if requested via AJAX
        if (request()->wantsJson()) {
            return response()->json($athlete);
        }

        return view('athletes.show', compact(
            'athlete',
            'testResults',
            'mentalAssessments',
            'taktikAssessments',
            'radarStats',
            'ratedTestCount',
            'screeningStats',
            'completedTestTypes',
            'latestTestDate'
        ));
    }
    public function update(Request $request, Athlete $athlete)
    {
        try {
            $player = User::where('athlete_id', $athlete->id)->first();
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'birth_place' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date|before_or_equal:today',
                'age' => 'nullable|integer|min:0|max:100',
                'gender' => 'nullable|in:M,F',
                'position' => ['required', Rule::in(Athlete::POSITIONS)],
                'height' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'phone' => [
                    $player ? 'required' : 'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9+\s().-]+$/',
                ],
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'Nama lengkap wajib diisi.',
                'name.max' => 'Nama lengkap maksimal 255 karakter.',
                'birth_place.max' => 'Tempat lahir maksimal 100 karakter.',
                'birth_date.date' => 'Format tanggal lahir tidak valid.',
                'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melewati hari ini.',
                'age.integer' => 'Usia harus berupa angka bulat.',
                'position.required' => 'Posisi wajib dipilih.',
                'position.in' => 'Posisi yang dipilih tidak valid.',
                'height.numeric' => 'Tinggi badan harus berupa angka.',
                'height.min' => 'Tinggi badan tidak boleh kurang dari 0.',
                'weight.numeric' => 'Berat badan harus berupa angka.',
                'weight.min' => 'Berat badan tidak boleh kurang dari 0.',
                'phone.max' => 'Nomor telepon maksimal 20 karakter.',
                'phone.required' => 'Nomor HP wajib diisi untuk akun pemain.',
                'phone.regex' => 'Format nomor HP tidak valid.',
                'photo.image' => 'Foto harus berupa file gambar.',
                'photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau GIF.',
                'photo.max' => 'Ukuran foto maksimal 2 MB.',
            ]);

            $data['phone'] = $this->normalizedPhone($data['phone'] ?? null, $player);

            if ($request->hasFile('photo')) {
                if ($athlete->photo && str_starts_with($athlete->photo, 'uploads/athletes/') && file_exists(public_path($athlete->photo))) {
                    unlink(public_path($athlete->photo));
                }

                $photo = $request->file('photo');
                $photoName = Str::uuid() . '.' . $photo->extension();
                $photo->move(public_path('uploads/athletes'), $photoName);
                $data['photo'] = 'uploads/athletes/' . $photoName;
            }

            DB::transaction(function () use ($athlete, $data) {
                $athlete->update($data);
                $this->syncPlayerAccount($athlete);
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'athlete' => $athlete]);
            }

            return redirect()->route('athletes.show', $athlete)
                ->with('success', 'Data atlet berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }
    }
    public function destroy($id)
    {
        $athlete = Athlete::findOrFail($id);
        $athlete->delete();

        return redirect()->route('athletes.index')->with('success', 'Atlet dihapus!');
    }
}
