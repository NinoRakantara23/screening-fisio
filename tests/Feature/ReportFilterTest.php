<?php

namespace Tests\Feature;

use App\Models\Athlete;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Fisioterapis Test',
            'email' => 'fisio@example.test',
            'password' => 'password',
            'role' => 'fisio',
        ]));
    }

    public function test_position_and_date_filters_update_rows_summaries_and_export_links(): void
    {
        $date = now()->toDateString();
        $forward = Athlete::create(['name' => 'Pemain Penyerang', 'position' => 'Penyerang']);
        $legacyForward = Athlete::create(['name' => 'Pemain Striker', 'position' => 'Striker']);
        $withoutTest = Athlete::create(['name' => 'Pemain Tanpa Tes', 'position' => 'Penyerang']);
        $midfielder = Athlete::create(['name' => 'Pemain Gelandang', 'position' => 'Gelandang']);

        $this->createResult($forward, 'endurance', 1000, $date . ' 08:00:00');
        $this->createResult($forward, 'endurance', 9000, now()->subDay()->toDateTimeString());
        $this->createResult($legacyForward, 'endurance', 3000, $date . ' 09:00:00');
        $this->createResult($midfielder, 'endurance', 6000, $date . ' 10:00:00');

        $this->get(route('report.index', ['position' => 'Penyerang', 'date' => $date]))
            ->assertOk()
            ->assertSee('Pemain Penyerang')
            ->assertSee('Pemain Striker')
            ->assertSee('Pemain Tanpa Tes')
            ->assertDontSee('Pemain Gelandang')
            ->assertSee('2,000m')
            ->assertSee('0/10')
            ->assertSee('value="' . $date . '"', false)
            ->assertSee('href="' . e(route('report.export.pdf', ['position' => 'Penyerang', 'date' => $date])) . '"', false)
            ->assertSee('href="' . e(route('report.export.excel', ['position' => 'Penyerang', 'date' => $date])) . '"', false);
    }

    public function test_date_filter_without_position_and_empty_position_results_are_supported(): void
    {
        $date = now()->toDateString();
        $goalkeeper = Athlete::create(['name' => 'Pemain Kiper', 'position' => 'Kiper']);
        $this->createResult($goalkeeper, 'speed', null, $date . ' 08:00:00');

        $this->get(route('report.index', ['date' => $date]))
            ->assertOk()
            ->assertSee('Pemain Kiper');

        $this->get(route('report.index', ['position' => 'Bek', 'date' => $date]))
            ->assertOk()
            ->assertSee('Tidak ada data untuk filter ini.')
            ->assertSee('YoYo Avg')
            ->assertSee('—');
    }

    private function createResult(Athlete $athlete, string $testType, ?float $distance, string $date): void
    {
        TestResult::create([
            'athlete_id' => $athlete->id,
            'name' => $athlete->name,
            'test_type' => $testType,
            'test_date' => $date,
            'distance' => $distance,
        ]);
    }
}