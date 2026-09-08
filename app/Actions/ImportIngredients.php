<?php

namespace App\Actions;

use App\Models\Ingredient;
use Maatwebsite\Excel\Facades\Excel;

class ImportIngredients
{
    /**
     * Файл прайса, поставляемый вместе с репозиторием.
     */
    public static function defaultFile(): string
    {
        return database_path('data/ingredients-cost-price.xlsx');
    }

    /**
     * Импорт ингредиентов из Excel/CSV.
     *
     * Ожидаемые колонки: Ingredients | Cost | Unit Size | Unit | Cost per kg
     *
     * @param  string|\Illuminate\Http\UploadedFile  $file
     * @return array{created: int, updated: int, skipped: array, errors: array}
     */
    public function handle($file, bool $dryRun = false): array
    {
        $rows = Excel::toArray([], $file)[0];

        $created = 0;
        $updated = 0;
        $skipped = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            // Пропускаем заголовок
            if ($i === 0) {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                continue;
            }

            $price = $this->toFloat($row[1] ?? null);
            $size = $this->toFloat($row[2] ?? null);

            if ($price === null || $size === null || $size <= 0) {
                $skipped[] = ['row' => $i + 1, 'name' => $name, 'reason' => 'не заполнены Cost / Unit Size'];

                continue;
            }

            try {
                $attributes = [
                    'price' => $price,
                    'size' => $size,
                    'kg_price' => $price / $size,
                    'unit' => $this->normalizeUnit((string) ($row[3] ?? '')),
                ];

                $existing = Ingredient::whereRaw('lower(name) = ?', [mb_strtolower($name)])->get();

                if ($existing->isNotEmpty()) {
                    if (! $dryRun) {
                        $existing->each->update($attributes);
                    }
                    $updated += $existing->count();
                } else {
                    if (! $dryRun) {
                        Ingredient::create($attributes + ['name' => $name]);
                    }
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = ['row' => $i + 1, 'name' => $name, 'error' => $e->getMessage()];
            }
        }

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function toFloat($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    private function normalizeUnit(string $unit): string
    {
        return match (strtolower(trim($unit))) {
            'l', 'liter', 'litre', 'liters', 'litres', 'ml' => 'l',
            'pcs', 'piece', 'pieces', 'nos.', 'nos', 'no.' => 'pcs',
            default => 'kg',
        };
    }
}
