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
    Route::get('/{ingredient}', [\App\Http\Controllers\IngredientsController::class, 'edit'])->name('ingredients.edit');
    Route::put('/{ingredient}', [\App\Http\Controllers\IngredientsController::class, 'update'])->name('ingredients.update');
    Route::delete('/{ingredient}', [\App\Http\Controllers\IngredientsController::class, 'destroy'])->name('ingredients.destroy');
});

Route::prefix('components')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ComponentsController::class, 'index'])->name('components');
    Route::get('/create', [\App\Http\Controllers\ComponentsController::class, 'create'])->name('components.create');
    Route::post('/', [\App\Http\Controllers\ComponentsController::class, 'store'])->name('components.store');
    Route::get('/{component}', [\App\Http\Controllers\ComponentsController::class, 'edit'])->name('components.edit');
    Route::put('/{component}', [\App\Http\Controllers\ComponentsController::class, 'update'])->name('components.update');
    Route::delete('/{component}', [\App\Http\Controllers\ComponentsController::class, 'destroy'])->name('components.destroy');
});


require __DIR__.'/settings.php';
