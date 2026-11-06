<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const POSITION_ALIASES = [
        'Penyerang' => ['penyerang', 'striker', 'forward', 'winger', 'sayap'],
        'Gelandang' => ['gelandang', 'playmaker', 'midfielder'],
        'Bek' => ['bek', 'defender'],
        'Kiper' => ['kiper', 'goalkeeper'],
    ];

    public function up(): void
    {
        foreach (['athletes', 'test_results'] as $table) {
            foreach (self::POSITION_ALIASES as $position => $aliases) {
                DB::table($table)
                    ->whereIn(DB::raw('LOWER(TRIM(position))'), $aliases)
                    ->update(['position' => $position]);
            }
        }
    }

    public function down(): void
    {
    }
};