<?php

namespace Tests\Feature;

use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticTestRatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_test_ratings_are_derived_from_measurements_on_the_server(): void
    {
        $physio = User::create([
            'name' => 'Fisioterapis',
            'email' => 'fisio@example.test',
            'password' => 'secret',
            'role' => 'fisio',
        ]);
        $this->actingAs($physio);

        $common = [
            'name' => 'Atlet Tes',
            'gender' => 'putra',
            'test_date' => '2026-11-17 10:00:00',
        ];
        $cases = [
            [
                'route' => 'forms.speed.store',
                'data' => ['time_seconds' => 7.0, 'score' => 1, 'rating' => 'Buruk'],
                'expected' => ['score' => 4, 'rating' => 'Baik'],
            ],
            [
                'route' => 'forms.agility.store',
                'data' => [
                    'hex_time1' => 11.2, 'hex_time2' => 11.2, 'hex_avg' => 99,
                    'hex_rating' => 'Rendah', 'ill_time' => 16.1, 'ill_rating' => 'Poor',
                ],
                'expected' => ['hex_avg' => '11.20', 'hex_rating' => 'Diatas Rata-rata', 'ill_rating' => 'Above Average'],
            ],
            [
                'route' => 'forms.strength.store',
                'data' => [
                    'pushup_count' => 30, 'pushup_score' => 1, 'pushup_rating' => 'Kurang',
                    'situp_count' => 95, 'situp_score' => 1, 'situp_rating' => 'Kurang',
                ],
                'expected' => ['pushup_score' => 4, 'pushup_rating' => 'Baik Sekali', 'situp_score' => 5, 'situp_rating' => 'Sempurna'],
            ],
            [
                'route' => 'forms.power.store',
                'data' => ['best' => 245, 'rating' => 'Sangat Buruk'],
                'expected' => ['rating' => 'Baik Sekali'],
            ],
            [
                'route' => 'forms.accuracy.store',
                'data' => [
                    'shot1_note' => 'sasaran', 'shot2_note' => 'sasaran', 'shot3_note' => 'tali',
                    'shot1_score' => 0, 'shot2_score' => 0, 'shot3_score' => 0, 'total_score' => 0, 'rating' => 'Poor',
                ],
                'expected' => ['shot1_score' => '2.0', 'shot2_score' => '2.0', 'shot3_score' => '1.0', 'total_score' => '5.0', 'rating' => 'Excellent'],
            ],
            [
                'route' => 'forms.reaction.store',
                'data' => ['value_cm' => 10, 'rating' => 'Poor'],
                'expected' => ['rating' => 'Above Average'],
            ],
            [
                'route' => 'forms.coordination.store',
                'data' => ['steps50' => 30, 'steps50_result' => 'normal', 'steps100' => 44.9, 'steps100_result' => 'abnormal'],
                'expected' => ['steps50_result' => 'abnormal', 'steps100_result' => 'normal'],
            ],
            [
                'route' => 'forms.flexibility.store',
                'data' => ['gender' => 'putri', 'best' => 31, 'rating' => 'Poor'],
                'expected' => ['rating' => 'Good'],
            ],
            [
                'route' => 'forms.balance.store',
                'data' => [
                    'leg_length' => 100,
                    'left_a' => 50, 'left_pm' => 60, 'left_pl' => 70,
                    'right_a' => 49, 'right_pm' => 61, 'right_pl' => 69,
                    'res_diff_anterior' => '999', 'std_anterior' => 'below', 'std_composite' => 'optimal',
                ],
                'expected' => [
                    'res_diff_anterior' => '1.0', 'std_anterior' => 'optimal',
                    'composite_left' => '60.0%', 'composite_right' => '59.7%',
                    'std_composite' => 'below', 'rating' => 'below',
                ],
            ],
        ];

        foreach ($cases as $case) {
            $this->post(route($case['route']), array_merge($common, $case['data']))
                ->assertRedirect();
            $result = TestResult::latest('id')->firstOrFail();
            foreach ($case['expected'] as $field => $expected) {
                $actual = $result->{$field};
                if (is_numeric($expected) && is_numeric($actual)) {
                    $this->assertEqualsWithDelta((float) $expected, (float) $actual, 0.001, "{$case['route']} {$field}");
                } else {
                    $this->assertSame((string) $expected, (string) $actual, "{$case['route']} {$field}");
                }
            }
        }
    }

    public function test_form_rating_fields_are_read_only_and_calculated_fields_are_not_user_editable(): void
    {
        $physio = User::create([
            'name' => 'Fisioterapis',
            'email' => 'fisio@example.test',
            'password' => 'secret',
            'role' => 'fisio',
        ]);

        $this->actingAs($physio)
            ->get(route('forms.speed'))
            ->assertOk()
            ->assertSee('id="speedRating"', false)
            ->assertSee('id="speedScore"', false)
            ->assertSee('readonly', false);

        $this->get(route('forms.balance'))
            ->assertOk()
            ->assertSee('std_composite_display', false)
            ->assertSee('diperbarui otomatis', false);
    }
}
