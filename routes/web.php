<?php

use App\Http\Controllers\GradeTransitionCompareController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
})->name('dashboard');

Route::get('/grade-change-trend', function()  {
    return Inertia::render('GradeChangeTrend');
})->name('grade-change-trend');

Route::get('/grade-transition-ui', function() {
    return Inertia::render('GradeTransition');
})->name('grade-transition-ui');

Route::get('/grade-transition-compare', [GradeTransitionCompareController::class, 'show'])
     ->name('grade-transition-compare');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
