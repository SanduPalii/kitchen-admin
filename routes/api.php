<?php
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::post('/ingredients/import', [\App\Http\Controllers\IngredientImportController::class, 'import']);
