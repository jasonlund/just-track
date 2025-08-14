<?php

namespace App\Console\Commands\FanArtTV;

use App\Models\Show;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FanArtTVUpdateImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fanarttv:update-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store images from FanArtTV for all initialized shows';

    /**
     * Execute the console command.
     */
    public function handle(ImageService $imageService)
    {

        $this->info('Fetching initialized shows with TVDB IDs...');

        // Get all initialized shows that have TVDB IDs (required for FanArtTV)
        $shows = Show::initialized()
            ->whereNotNull('tvdb_id')
            ->get();

        if ($shows->isEmpty()) {
            $this->info('No initialized shows with TVDB IDs found.');

            return Command::SUCCESS;
        }

        $this->info('Found '.$shows->count().' initialized shows to process.');

        $progressBar = $this->output->createProgressBar($shows->count());
        $totalImages = 0;
        $errors = 0;

        foreach ($shows as $show) {
            $progressBar->advance();

            try {
                $imageCount = $imageService->fetchAndStoreShowImages($show);
                $totalImages += $imageCount;

                if ($imageCount > 0) {
                    $this->line(' - '.$show->name.': '.$imageCount.' images');
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to fetch images for show '.$show->name.' (TVDB: '.$show->tvdb_id.'): '.$e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('Image update complete!');
        $this->info('Processed '.$totalImages.' total images for '.$shows->count().' shows.');

        if ($errors > 0) {
            $this->warn('Encountered '.$errors.' errors during processing.');
        }

        return Command::SUCCESS;
    }
}
