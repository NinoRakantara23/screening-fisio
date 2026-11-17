<?php

namespace Database\Seeders;

use App\Models\Athlete;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil atlet pertama
        $first = Athlete::first();

        if (!$first) {
            $this->command->warn(
                'Belum ada data atlet, PlayerSeeder dilewati.'
            );

            return;
        }

        Athlete::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->each(function (Athlete $athlete) {
                $phone = User::normalizePhone($athlete->phone);

                if (!preg_match('/^0[0-9]{8,14}$/', $phone)) {
                    $this->command->warn(
                        "Akun {$athlete->name} dilewati: nomor HP tidak valid."
                    );
                    return;
                }

                $existingPlayer = User::where('athlete_id', $athlete->id)->first();
                if ($existingPlayer) {
                    if ($existingPlayer->role === 'pemain') {
                        if (User::where('phone', $phone)->where('id', '!=', $existingPlayer->id)->exists()) {
                            $this->command->warn(
                                "Akun {$athlete->name} dilewati: nomor HP sudah digunakan."
                            );
                            return;
                        }

                        $existingPlayer->update([
                            'name' => $athlete->name,
                            'phone' => $phone,
                        ]);
                    }

                    return;
                }

                if (User::where('phone', $phone)->exists()) {
                    $this->command->warn(
                        "Akun {$athlete->name} dilewati: nomor HP sudah digunakan."
                    );
                    return;
                }

                User::create([
                    'name'       => $athlete->name,
                    'email'      => $phone . '@pemain.footscreen.com',
                    'phone'      => $phone,
                    'password'   => $phone,
                    'role'       => 'pemain',
                    'athlete_id' => $athlete->id,
                ]);
            });

        if (
            (!$first->phone || !preg_match('/^0[0-9]{8,14}$/', User::normalizePhone($first->phone)))
            && !User::where('athlete_id', $first->id)->exists()
            && !User::where('email', 'pemain@footscreen.com')->exists()
        ) {
            User::create([
                'name'       => $first->name,
                'email'      => 'pemain@footscreen.com',
                'password'   => 'password123',
                'role'       => 'pemain',
                'athlete_id' => $first->id,
            ]);
        }

        $this->command->info(
            'Seeder akun pemain berhasil dijalankan.'
        );
    }
}