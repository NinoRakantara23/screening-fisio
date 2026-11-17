<?php

namespace Tests\Feature;

use App\Models\Athlete;
use App\Models\TestResult;
use App\Models\User;
use Database\Seeders\PlayerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlayerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_an_athlete_with_a_phone_creates_a_player_login(): void
    {
        $physio = User::create([
            'name' => 'Fisioterapis',
            'email' => 'fisio@example.test',
            'password' => 'secret',
            'role' => 'fisio',
        ]);

        $this->actingAs($physio)
            ->post(route('athletes.store'), [
                'name' => 'Aldo Pratama',
                'birth_place' => 'Ntt',
                'birth_date' => '2002-12-25',
                'age' => 23,
                'gender' => 'M',
                'height' => 160,
                'weight' => 50,
                'phone' => '+62 812-3456-7890',
            ])
            ->assertRedirect(route('athletes.index'));

        $athlete = Athlete::where('name', 'Aldo Pratama')->firstOrFail();
        $player = User::where('athlete_id', $athlete->id)->firstOrFail();

        $this->assertSame('pemain', $player->role);
        $this->assertSame('081234567890', $player->phone);
        $this->assertTrue(Hash::check('081234567890', $player->password));

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login'), [
            'login' => '+62 812-3456-7890',
            'password' => '081234567890',
        ])->assertRedirect(route('player.dashboard'));

        $this->get(route('player.tests'))->assertOk();
        $this->get(route('player.profile'))
            ->assertOk()
            ->assertSee('Data Fisik')
            ->assertSee('Tempat, Tanggal Lahir')
            ->assertSee('Ntt')
            ->assertSee('23 tahun')
            ->assertSee('Laki-laki')
            ->assertSee('160.0 cm')
            ->assertSee('50.0 kg')
            ->assertSee('0 Tes Tercatat')
            ->assertDontSee('Edit Data')
            ->assertDontSee('Hapus');

        $this->put(route('player.profile.update'), [
            'phone' => '081398765432',
            'height' => null,
            'weight' => null,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('athletes', [
            'id' => $athlete->id,
            'phone' => '081398765432',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $player->id,
            'phone' => '081398765432',
        ]);

        $this->post(route('logout'));
        $this->post(route('login'), [
            'login' => '081398765432',
            'password' => '081234567890',
        ])->assertRedirect(route('player.dashboard'));
    }

    public function test_player_only_sees_test_results_linked_to_their_athlete_id(): void
    {
        $athlete = Athlete::create(['name' => 'Pemain Sama']);
        $otherAthlete = Athlete::create(['name' => 'Pemain Sama']);
        $player = User::create([
            'name' => $athlete->name,
            'email' => '081234567890@pemain.footscreen.com',
            'phone' => '081234567890',
            'password' => 'secret',
            'role' => 'pemain',
            'athlete_id' => $athlete->id,
        ]);

        TestResult::create([
            'athlete_id' => $athlete->id,
            'test_type' => 'speed',
            'name' => $athlete->name,
            'test_date' => '2026-11-17 10:00:00',
        ]);
        TestResult::create([
            'athlete_id' => $otherAthlete->id,
            'test_type' => 'strength',
            'name' => $otherAthlete->name,
            'test_date' => '2026-11-17 11:00:00',
        ]);
        TestResult::create([
            'test_type' => 'agility',
            'name' => $athlete->name,
            'test_date' => '2026-11-17 12:00:00',
        ]);

        $this->actingAs($player)
            ->get(route('player.tests'))
            ->assertOk()
            ->assertViewHas('latest', function ($latest) {
                return $latest->has('speed')
                    && !$latest->has('strength')
                    && !$latest->has('agility');
            });
    }

    public function test_unambiguous_legacy_test_results_remain_visible(): void
    {
        $athlete = Athlete::create(['name' => 'Atlet Lama']);
        $player = User::create([
            'name' => $athlete->name,
            'email' => '081234567890@pemain.footscreen.com',
            'phone' => '081234567890',
            'password' => 'secret',
            'role' => 'pemain',
            'athlete_id' => $athlete->id,
        ]);

        TestResult::create([
            'test_type' => 'speed',
            'name' => $athlete->name,
            'test_date' => '2026-11-17 10:00:00',
        ]);

        $this->actingAs($player)
            ->get(route('player.tests'))
            ->assertOk()
            ->assertViewHas('latest', fn($latest) => $latest->has('speed'));
    }

    public function test_player_seeder_creates_logins_for_existing_athletes_with_phones(): void
    {
        $athlete = Athlete::create([
            'name' => 'Atlet Lama',
            'phone' => '+62 812-3456-7890',
        ]);

        $this->seed(PlayerSeeder::class);

        $player = User::where('athlete_id', $athlete->id)->firstOrFail();

        $this->assertSame('pemain', $player->role);
        $this->assertSame('081234567890', $player->phone);
        $this->assertTrue(Hash::check('081234567890', $player->password));
    }
}
