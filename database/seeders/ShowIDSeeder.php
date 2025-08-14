<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ShowIDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding shows table with TV Maze data...');

        // Get the JSON data
        $jsonPath = database_path('seeders/data/show-ids-2025-08-14.json');

        if (! File::exists($jsonPath)) {
            $this->command->error("Shows data file not found at: {$jsonPath}");
            $this->command->info('Please ensure the show-ids-2025-08-14.json file exists in database/seeders/data/');

            return;
        }

        $shows = json_decode(File::get($jsonPath), true);

        if (empty($shows)) {
            $this->command->warn('No shows data found in the JSON file.');

            return;
        }

        $this->command->info('Found '.count($shows).' shows to import.');

        // Clear existing shows
        DB::table('shows')->truncate();

        // Process in chunks for better performance
        $chunks = array_chunk($shows, 1000);
        $bar = $this->command->getOutput()->createProgressBar(count($chunks));

        foreach ($chunks as $chunk) {
            $data = [];
            foreach ($chunk as $show) {
                // Handle nullable fields
                $data[] = [
                    'external_id' => $show['external_id'],
                    'name' => $show['name'],
                    'type' => $show['type'],
                    'language' => $show['language'],
                    'status' => $show['status'],
                    'runtime' => $show['runtime'],
                    'average_runtime' => $show['average_runtime'],
                    'premiered' => $show['premiered'],
                    'ended' => $show['ended'],
                    'tvdb_id' => $show['tvdb_id'],
                    'imdb_id' => $show['imdb_id'],
                    'image' => $show['image'],
                    'summary' => $show['summary'],
                    'external_updated_at' => $show['external_updated_at'],
                    'created_at' => $show['created_at'] ?? now(),
                    'updated_at' => $show['updated_at'] ?? now(),
                    'initialized' => $show['initialized'] ?? 0,
                ];
            }

            DB::table('shows')->insert($data);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Shows table seeded successfully!');
    }
}
