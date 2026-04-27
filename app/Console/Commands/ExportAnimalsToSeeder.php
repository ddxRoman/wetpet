<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnimalDetail;
use Illuminate\Support\Facades\File;

class ExportAnimalsToSeeder extends Command
{
    protected $signature = 'export:animals';
    protected $description = 'Экспорт animal_details в формате существующего сидера';

    public function handle()
    {
        // Загружаем детали вместе со связанной породой
        $details = AnimalDetail::with('animal')->get();
        
        $output = "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Seeder;\nuse App\Models\AnimalDetail;\nuse App\Models\Animal;\nuse Illuminate\Support\Facades\DB;\n\n";
        $output .= "class BackupAnimalDetailsSeeder extends Seeder\n{\n";
        $output .= "    public function run(): void\n    {\n";
        $output .= "        DB::statement('SET FOREIGN_KEY_CHECKS=0;');\n";
        $output .= "        DB::table('animal_details')->truncate();\n";
        $output .= "        DB::statement('SET FOREIGN_KEY_CHECKS=1;');\n\n";
        
        $output .= "        \$data = [\n";

        foreach ($details as $detail) {
            // Берем название породы из связи, если она есть
            $breedName = $detail->animal ? $detail->animal->breed : 'Unknown';
            $features = var_export($detail->features, true);

            $output .= "            [\n";
            $output .= "                'animal_breed' => '" . addslashes($breedName) . "',\n";
            $output .= "                'weight_range' => '" . addslashes($detail->weight_range) . "',\n";
            $output .= "                'height_range' => '" . addslashes($detail->height_range) . "',\n";
            $output .= "                'lifespan' => '" . addslashes($detail->lifespan) . "',\n";
            $output .= "                'type' => '" . addslashes($detail->type) . "',\n";
            $output .= "                'photo' => '{$detail->photo}',\n";
            $output .= "                'short_description' => '" . addslashes($detail->short_description) . "',\n";
            $output .= "                'full_description' => \"" . addslashes($detail->full_description) . "\",\n";
            $output .= "                'features' => {$features},\n";
            $output .= "            ],\n";
        }

        $output .= "        ];\n\n";

        $output .= "        foreach (\$data as \$item) {\n";
        $output .= "            \$animal = Animal::where('breed', \$item['animal_breed'])->first();\n\n";
        $output .= "            if (!\$animal) {\n";
        $output .= "                \$this->command->warn(\"Животное с породой '{\$item['animal_breed']}' не найдено. Пропускаем...\");\n";
        $output .= "                continue;\n";
        $output .= "            }\n\n";
        $output .= "            \$cleanData = \$item;\n";
        $output .= "            \$cleanData['animal_breed'] = \$animal->id;\n\n";
        $output .= "            AnimalDetail::updateOrCreate(\n";
        $output .= "                ['animal_breed' => \$cleanData['animal_breed']],\n";
        $output .= "                \$cleanData\n";
        $output .= "            );\n";
        $output .= "        }\n";
        $output .= "    }\n}\n";

        File::put(database_path('seeders/BackupAnimalDetailsSeeder.php'), $output);
        $this->info('Данные экспортированы в BackupAnimalDetailsSeeder.php');
    }
}