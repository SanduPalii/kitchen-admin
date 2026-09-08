<?php

namespace App\Http\Controllers;

use App\Actions\ImportIngredients;
use Illuminate\Http\Request;

class IngredientImportController extends Controller
{
    public function import(Request $request, ImportIngredients $import)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $result = $import->handle($request->file('file'));

        return response()->json(['success' => true] + $result);
    }
}
