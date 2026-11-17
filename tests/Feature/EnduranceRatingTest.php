<?php

namespace Tests\Feature;

use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnduranceRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_endurance_rating_is_derived_from_distance_and_not_the_submitted_rating(): void
    {
        $physio = User::create([
            'name' => 'Fisioterapis',
            'email' => 'fisio@example.test',
            'password' => 'secret',
            'role' => 'fisio',
        ]);

        $this->actingAs($physio)
            ->get(route('forms.endurance'))
            ->assertOk()
            ->assertSee('Penilaian Otomatis')
            ->assertSee('Sangat Baik')
            ->assertSee('Di Bawah Rata-rata')
            ->assertSee('readonly');

        $cases = [
            2401 => 'Elite',
            2400 => 'Excellent',
            2000 => 'Excellent',
            1999 => 'Good',
            1520 => 'Good',
            1519 => 'Average',
            1040 => 'Average',
            1039 => 'Below Average',
            520 => 'Below Average',
            519 => 'Very Poor',
        ];

        foreach ($cases as $distance => $expectedRating) {
            $this->post(route('forms.endurance.store'), [
                'name' => 'Atlet Tes',
                'gender' => 'putra',
                'test_date' => '2026-11-17 10:00:00',
                'distance' => $distance,
                'rating' => 'Manual Rating',
            ])->assertRedirect(route('forms.endurance'));

            $this->assertSame($expectedRating, TestResult::latest('id')->value('rating'));
        }
    }
}
