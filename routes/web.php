<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MentalController;
use App\Http\Controllers\TaktikController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PlayerController;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA = LOGIN
| Membuka "/" selalu menampilkan halaman login (sesi lama dihapus).
|--------------------------------------------------------------------------
*/
Route::get('/', function (Request $request) {
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (requires login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /* ---- Dashboard (Admin, Fisio, Pelatih) ---- */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index')->middleware('role:fisio,pelatih');

    /* ---- Athletes ---- */
    Route::get('/athletes', [AthleteController::class, 'index'])
        ->name('athletes.index')->middleware('role:fisio,pelatih');
    Route::get('/athletes/{id}', [AthleteController::class, 'show'])
        ->name('athletes.show')->middleware('role:fisio,pelatih');

    Route::post('/athletes', [AthleteController::class, 'store'])->name('athletes.store')->middleware('role:fisio');
    Route::put('/athletes/{id}', [AthleteController::class, 'update'])->name('athletes.update')->middleware('role:fisio');
    Route::delete('/athletes/{id}', [AthleteController::class, 'destroy'])->name('athletes.destroy')->middleware('role:fisio');

    /* ---- SCREENING FISIOTERAPI (Fisio & Admin) ---- */
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index')->middleware('role:fisio');

    $forms = [
        'endurance', 'speed', 'agility', 'strength', 'power',
        'accuracy', 'reaction', 'coordination', 'flexibility', 'balance',
    ];

    foreach ($forms as $form) {
        Route::get("/forms/{$form}", [TestController::class, "form_" . $form])
            ->name("forms.{$form}")->middleware('role:fisio');
        Route::post("/forms/{$form}", [TestController::class, "store_" . $form])
            ->name("forms.{$form}.store")->middleware('role:fisio');
    }

    /* ---- REPORT FISIOTERAPI (Fisio & Admin) ---- */
    Route::prefix('report')->middleware('role:fisio')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');
        Route::get('/athlete/{id}', [ReportController::class, 'athleteReport'])->name('report.athlete');
    });

    /* ---- ASSESSMENT MENTAL & TAKTIK (Pelatih & Admin) ---- */
    Route::middleware('role:pelatih')->group(function () {
        Route::resource('mental', MentalController::class);
        Route::resource('taktik', TaktikController::class);
    });

    /* ---- REPORTS PELATIH (Pelatih & Admin) ---- */
    Route::prefix('reports')->middleware('role:pelatih')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/export/pdf', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/export/excel', [ReportsController::class, 'exportExcel'])->name('reports.export.excel');
    });

    /* ---- AREA PEMAIN (read-only, data milik sendiri) ---- */
    Route::prefix('pemain')->name('player.')->middleware('role:pemain')->group(function () {
        Route::get('/', [PlayerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [PlayerController::class, 'profile'])->name('profile');
        Route::put('/profil', [PlayerController::class, 'updateProfile'])->name('profile.update');
        Route::get('/hasil-fisioterapi', [PlayerController::class, 'tests'])->name('tests');
        Route::get('/assessment-mental', [PlayerController::class, 'mental'])->name('mental');
        Route::get('/assessment-taktik', [PlayerController::class, 'taktik'])->name('taktik');
        Route::get('/laporan-pdf', [PlayerController::class, 'pdf'])->name('pdf');
    });
});