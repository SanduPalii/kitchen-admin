<?php

namespace Database\Seeders;

use App\Actions\ImportIngredients;
use Illuminate\Database\Seeder;

class IngredientsSeeder extends Seeder
{
    public function run(ImportIngredients $import): void
    {
        $file = ImportIngredients::defaultFile();

        if (! is_file($file)) {
            $this->command?->warn("Файл прайса не найден: {$file}");

            return;
        }

        $result = $import->handle($file);

        $this->command?->info("Ингредиенты: создано {$result['created']}, обновлено {$result['updated']}, пропущено ".count($result['skipped']));
    }
}
