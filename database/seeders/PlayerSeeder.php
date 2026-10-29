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

        // ==========================================
        // 1. AKUN DEMO PEMAIN (QUICK LOGIN)
        // ==========================================
        User::updateOrCreate(
            ['email' => 'pemain@footscreen.com'],
            [
                'name'       => $first->name,
                'phone'      => $first->phone
                    ? User::normalizePhone($first->phone)
                    : null,
                'password'   => 'password123',
                'role'       => 'pemain',
                'athlete_id' => $first->id,
            ]
        );

        // ==========================================
        // 2. AKUN UNTUK ATLET LAIN YANG PUNYA NO. HP
        // ==========================================
        Athlete::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->each(function (Athlete $athlete) {
                $phone = User::normalizePhone($athlete->phone);

                // Lewati jika atlet sudah memiliki akun
                // atau nomor HP sudah digunakan akun lain
                if (
                    User::where('athlete_id', $athlete->id)
                        ->orWhere('phone', $phone)
                        ->exists()
                ) {
                    return;
                }

                // Buat akun pemain
                User::create([
                    'name'       => $athlete->name,
                    'email'      => $phone . '@pemain.footscreen.com',
                    'phone'      => $phone,
                    'password'   => $phone,
                    'role'       => 'pemain',
                    'athlete_id' => $athlete->id,
                ]);
            });

        $this->command->info(
            'Seeder akun pemain berhasil dijalankan.'
        );
    }
}