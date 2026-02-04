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

Route::prefix('products')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ProductsController::class, 'index'])->name('products');
    Route::get('/create', [\App\Http\Controllers\ProductsController::class, 'create'])->name('products.create');
    Route::post('/', [\App\Http\Controllers\ProductsController::class, 'store'])->name('products.store');
    Route::get('/{product}/edit', [\App\Http\Controllers\ProductsController::class, 'edit'])->name('products.edit');
    Route::put('/{product}', [\App\Http\Controllers\ProductsController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [\App\Http\Controllers\ProductsController::class, 'destroy'])->name('products.destroy');
});

Route::prefix('locations')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\LocationsController::class, 'index'])->name('locations');
    Route::get('/create', [\App\Http\Controllers\LocationsController::class, 'create'])->name('locations.create');
    Route::post('/', [\App\Http\Controllers\LocationsController::class, 'store'])->name('locations.store');
    Route::get('/{location}/edit', [\App\Http\Controllers\LocationsController::class, 'edit'])->name('locations.edit');
    Route::put('/{location}', [\App\Http\Controllers\LocationsController::class, 'update'])->name('locations.update');
    Route::delete('/{location}', [\App\Http\Controllers\LocationsController::class, 'destroy'])->name('locations.destroy');
});

Route::prefix('clients')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ClientsController::class, 'index'])->name('clients');
    Route::get('/create', [\App\Http\Controllers\ClientsController::class, 'create'])->name('clients.create');
    Route::post('/', [\App\Http\Controllers\ClientsController::class, 'store'])->name('clients.store');
    Route::get('/{client}/edit', [\App\Http\Controllers\ClientsController::class, 'edit'])->name('clients.edit');
    Route::put('/{client}', [\App\Http\Controllers\ClientsController::class, 'update'])->name('clients.update');
    Route::delete('/{client}', [\App\Http\Controllers\ClientsController::class, 'destroy'])->name('clients.destroy');
});


require __DIR__.'/settings.php';
