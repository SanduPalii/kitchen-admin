<?php

namespace App\Console\Commands;

use App\Actions\ImportIngredients;
use Illuminate\Console\Command;

class ImportIngredientsCommand extends Command
{
    protected $signature = 'ingredients:import
                            {file? : Путь к файлу xlsx/xls/csv (по умолчанию database/data/ingredients-cost-price.xlsx)}
                            {--dry-run : Показать результат без записи в базу}';

    protected $description = 'Импорт ингредиентов из Excel-файла (Ingredients | Cost | Unit Size | Unit)';

    public function handle(ImportIngredients $import): int
    {
        $file = $this->argument('file') ?? ImportIngredients::defaultFile();

        if (! is_file($file)) {
            $this->error("Файл не найден: {$file}");

            return self::FAILURE;
        }

        $this->line("Файл: {$file}");

        $result = $import->handle($file, (bool) $this->option('dry-run'));

        if ($this->option('dry-run')) {
            $this->warn('Режим --dry-run: в базу ничего не записано.');
        }

        $this->info("Создано: {$result['created']}, обновлено: {$result['updated']}");

        if ($result['skipped']) {
            $this->warn('Пропущено строк: '.count($result['skipped']));
            $this->table(['Строка', 'Название', 'Причина'], array_map('array_values', $result['skipped']));
        }

        if ($result['errors']) {
            $this->error('Ошибок: '.count($result['errors']));
            $this->table(['Строка', 'Название', 'Ошибка'], array_map('array_values', $result['errors']));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
