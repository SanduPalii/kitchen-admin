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

Route::get('ingredients', function () {
    return Inertia::render('Ingredients');
})->middleware(['auth', 'verified'])->name('ingredients');

require __DIR__.'/settings.php';
