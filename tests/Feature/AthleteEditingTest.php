<?php

namespace Tests\Feature;

use App\Models\Athlete;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AthleteEditingTest extends TestCase
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

    public function test_edit_page_shows_current_athlete_data(): void
    {
        $athlete = Athlete::create([
            'name' => 'Pemain Awal',
            'position' => 'Striker',
            'gender' => 'M',
            'birth_place' => 'Bandung',
            'birth_date' => '2000-05-10',
            'age' => 26,
        ]);

        $this->get(route('athletes.edit', $athlete))
            ->assertOk()
            ->assertSee('value="Pemain Awal"', false)
            ->assertSee('value="Bandung"', false)
            ->assertSee('name="_method" value="PUT"', false);

        $this->get(route('athletes.index'))
            ->assertOk()
            ->assertSee('href="' . route('athletes.edit', $athlete) . '"', false);
    }

    public function test_athlete_can_be_updated_and_redirects_to_detail(): void
    {
        $athlete = Athlete::create([
            'name' => 'Pemain Awal',
            'position' => 'Striker',
        ]);

        $this->put(route('athletes.update', $athlete), [
            'name' => 'Pemain Baru',
            'position' => 'Gelandang',
            'gender' => 'F',
            'birth_place' => 'Jakarta',
            'birth_date' => '2001-04-12',
            'age' => 25,
            'height' => 170.5,
            'weight' => 62.3,
            'phone' => '081234567890',
        ])
            ->assertRedirect(route('athletes.show', $athlete))
            ->assertSessionHas('success', 'Data atlet berhasil diperbarui');

        $this->assertDatabaseHas('athletes', [
            'id' => $athlete->id,
            'name' => 'Pemain Baru',
            'position' => 'Gelandang',
            'gender' => 'F',
            'birth_place' => 'Jakarta',
            'birth_date' => '2001-04-12',
            'age' => 25,
            'height' => 170.5,
            'weight' => 62.3,
            'phone' => '081234567890',
        ]);

        $this->get(route('athletes.show', $athlete))
            ->assertOk()
            ->assertSee('Data atlet berhasil diperbarui')
            ->assertSee('Pemain Baru')
            ->assertSee('Perempuan')
            ->assertSee('Jakarta')
            ->assertSee('href="' . route('athletes.edit', $athlete) . '"', false);
    }

    public function test_invalid_athlete_update_returns_to_edit_with_errors_and_input(): void
    {
        $athlete = Athlete::create([
            'name' => 'Pemain Awal',
            'position' => 'Striker',
        ]);

        $this->from(route('athletes.edit', $athlete))
            ->put(route('athletes.update', $athlete), [
                'name' => '',
                'position' => 'Gelandang',
                'phone' => '081234567890',
            ])
            ->assertRedirect(route('athletes.edit', $athlete))
            ->assertSessionHasErrors('name')
            ->assertSessionHas('_old_input.phone', '081234567890');
    }
}