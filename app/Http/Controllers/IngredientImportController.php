<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IngredientImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $rows = Excel::toArray([], $request->file('file'))[0]; // первая страница Excel

        $created = 0;
        $errors = [];

        foreach ($rows as $i => $row) {

            // Пропускаем заголовок
            if ($i === 0) continue;

            try {
                $kg_price = (float) $row[1] / (float) $row[2];
                Ingredient::create([
                    'name'     => trim($row[0]),
                    'price'    => (float) $row[1],
                    'size'     => (float) $row[2],
                    'kg_price' => $kg_price,
                    'unit'     => $this->normalizeUnit($row[4]),
                ]);

                $created++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'row' => $i + 1,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'errors'  => $errors
        ]);
    }

    private function normalizeUnit(string $unit): string
    {
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'kg', 'килограмм', 'kilogram' => 'kg',
            'l', 'liter', 'литр' => 'l',
            'pcs', 'piece', 'шт' => 'pcs',
            default => 'kg',
        };
    }
}
