<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('calculator', function () {
    return Inertia::render('Calculator');
})->middleware(['auth', 'verified'])->name('calculator');

Route::prefix('ingredients')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\IngredientsController::class, 'getData'])->name('ingredients');
    Route::get('/create', [\App\Http\Controllers\IngredientsController::class, 'create'])->name('ingredients.create');
    Route::post('/', [\App\Http\Controllers\IngredientsController::class, 'store'])->name('ingredients.store');
    Route::delete('/{ingredient}', [\App\Http\Controllers\IngredientsController::class, 'destroy'])->name('ingredients.destroy');
});

require __DIR__.'/settings.php';
