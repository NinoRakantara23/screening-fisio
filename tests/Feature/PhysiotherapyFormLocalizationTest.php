<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhysiotherapyFormLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_menu_and_all_ten_form_titles_are_in_indonesian(): void
    {
        $physio = User::create([
            'name' => 'Fisioterapis',
            'email' => 'fisio@example.test',
            'password' => 'secret',
            'role' => 'fisio',
        ]);

        $this->actingAs($physio)
            ->get(route('tests.index'))
            ->assertOk()
            ->assertSee('DAYA TAHAN')
            ->assertSee('KECEPATAN')
            ->assertSee('KESEIMBANGAN');

        $forms = [
            'forms.endurance' => 'TES DAYA TAHAN YO-YO',
            'forms.speed' => 'TES LARI SPRINT 50 METER',
            'forms.agility' => 'TES KELINCAHAN',
            'forms.strength' => 'TES KEKUATAN',
            'forms.power' => 'DAYA LEDAK — LOMPAT JAUH TANPA AWALAN',
            'forms.accuracy' => 'TES KETEPATAN TENDANGAN',
            'forms.reaction' => 'TES KECEPATAN REAKSI',
            'forms.coordination' => 'TES LANGKAH FUKUDA — KOORDINASI',
            'forms.flexibility' => 'TES KELENTURAN PERGELANGAN KAKI',
            'forms.balance' => 'TES KESEIMBANGAN Y',
        ];

        foreach ($forms as $route => $title) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($title);
        }
    }
}
