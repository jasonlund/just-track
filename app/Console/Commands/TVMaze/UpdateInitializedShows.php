<?php

namespace App\Console\Commands\TVMaze;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Services\SeasonService;
use App\Services\TVMazeService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateInitializedShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tvmaze:update-initialized {--use-fixture : Use local fixture file instead of API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all initialized shows with latest episode data from TVMaze';

    /**
     * The TVMaze service instance.
     *
     * @var TVMazeService
     */
    protected $tvMazeService;

    /**
     * The Season service instance.
     *
     * @var SeasonService
     */
    protected $seasonService;

    /**
     * Execute the console command.
     */
    public function handle(TVMazeService $tvMazeService, SeasonService $seasonService)
    {
        $this->tvMazeService = $tvMazeService;
        $this->seasonService = $seasonService;

        $this->info('Fetching initialized shows...');

        // Get all initialized shows with their data for comparison
        $initializedShows = Show::initialized()
            ->get()
            ->keyBy('external_id');

        if ($initializedShows->isEmpty()) {
            $this->info('No initialized shows found.');

            return Command::SUCCESS;
        }

        $this->info('Found '.$initializedShows->count().' initialized shows to update.');

        $tempFile = null;

        try {
            if ($this->option('use-fixture')) {
                // Use the fixture file for testing
                $fixtureFile = base_path('tests/Fixtures/Http/TVMaze/schedule_full.json');
                if (! file_exists($fixtureFile)) {
                    $this->error('Fixture file not found: '.$fixtureFile);

                    return Command::FAILURE;
                }
                $this->info('Using fixture file...');
                $tempFile = $fixtureFile;
            } else {
                // Download from API
                $this->info('Fetching schedule data from TVMaze...');
                $tempFile = storage_path('app/tvmaze_schedule_'.date('Y-m-d_His').'.json');

                if (! $this->tvMazeService->scheduleFull($tempFile)) {
                    $this->error('Failed to fetch schedule data from TVMaze.');

                    return Command::FAILURE;
                }
            }

            $this->info('Processing schedule data...');

            // Process the file
            $processedEpisodes = 0;
            $updatedShows = collect();
            $errors = 0;

            // Get file size for memory check
            $fileSize = filesize($tempFile);
            $this->info('Processing '.round($fileSize / 1024 / 1024, 2).' MB of data...');

            // Read and decode the JSON file
            $content = file_get_contents($tempFile);
            $allEpisodes = json_decode($content, true);

            if (! is_array($allEpisodes)) {
                throw new \Exception('Invalid JSON response from TVMaze');
            }

            $this->info('Found '.count($allEpisodes).' total episodes in schedule.');

            // Filter episodes to only those from initialized shows
            $episodes = collect($allEpisodes)->filter(function ($episodeData) use ($initializedShows) {
                return isset($episodeData['_embedded']['show']['id'])
                    && $initializedShows->has($episodeData['_embedded']['show']['id']);
            });

            $this->info('Processing '.$episodes->count().' episodes from initialized shows.');

            $progressBar = $this->output->createProgressBar($episodes->count());

            foreach ($episodes as $episodeData) {
                $progressBar->advance();

                try {
                    $this->processEpisode($episodeData, $initializedShows, $processedEpisodes, $updatedShows);
                } catch (\Exception $e) {
                    // Errors are already logged in processEpisode()
                    $errors++;
                }
            }

            $progressBar->finish();
            $this->newLine();

            // Update the external_updated_at timestamp for all updated shows
            if ($updatedShows->isNotEmpty()) {
                Show::whereIn('external_id', $updatedShows->keys())
                    ->update(['external_updated_at' => now()]);
            }

            $this->info('Update complete!');
            $this->info('Processed '.$processedEpisodes.' episodes for '.$updatedShows->count().' shows.');

            if ($errors > 0) {
                $this->warn('Encountered '.$errors.' errors during processing.');
            }

        } finally {
            // Clean up the temporary file (only if we created it from API)
            if ($tempFile && ! $this->option('use-fixture') && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Process a single episode from the schedule data
     */
    private function processEpisode(array $episodeData, Collection $initializedShows, int &$processedEpisodes, Collection &$updatedShows): void
    {
        $showData = $episodeData['_embedded']['show'];
        $showExternalId = $showData['id'];
        $show = $initializedShows->get($showExternalId);
        $showId = $show->id;

        // Transaction for show update
        if (! $updatedShows->has($showExternalId)) {
            DB::beginTransaction();
            try {
                // Check if show information has changed and update if needed
                $newImage = $showData['image']['original'] ?? null;

                if ($show->name !== $showData['name'] ||
                    $show->status !== $showData['status'] ||
                    $show->premiered !== $showData['premiered'] ||
                    $show->ended !== $showData['ended'] ||
                    $show->summary !== $showData['summary'] ||
                    $show->image !== $newImage) {

                    Show::where('id', $showId)->update([
                        'name' => $showData['name'],
                        'status' => $showData['status'],
                        'premiered' => $showData['premiered'],
                        'ended' => $showData['ended'],
                        'summary' => $showData['summary'],
                        'image' => $newImage,
                    ]);

                    // Mark this show as updated
                    $updatedShows->put($showExternalId, true);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update show: '.$e->getMessage(), ['show_id' => $showExternalId]);
            }
        }

        // Find or create the season
        if (! $season = Season::where('show_id', $showId)
            ->where('number', $episodeData['season'])
            ->first()) {

            // Transaction for season creation
            DB::beginTransaction();
            try {
                // Fetch season data from API to get the external_id
                $showDataWithSeasons = $this->tvMazeService->show($showExternalId);
                $seasons = $showDataWithSeasons['_embedded']['seasons'] ?? [];

                // Find the specific season we need
                $seasonData = collect($seasons)->firstWhere('number', $episodeData['season']);

                if (! $seasonData) {
                    Log::warning("Season {$episodeData['season']} not found in API response for show {$showExternalId} - skipping episode");
                    DB::rollBack();

                    return;
                }

                // Create the season with the data from API
                $season = $this->seasonService->create($show, $seasonData);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::warning('Failed to fetch/create season for show '.$showExternalId.': '.$e->getMessage().' - skipping episode');

                return;
            }
        }

        // Transaction for episode creation/update
        DB::beginTransaction();
        try {
            Episode::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'external_id' => $episodeData['id'],
                ],
                [
                    'number' => $episodeData['number'],
                    'name' => $episodeData['name'],
                    'type' => $episodeData['type'] ?? 'regular',
                    'premiered' => $episodeData['airdate'],
                    'air_timestamp' => $episodeData['airstamp'],
                    'runtime' => $episodeData['runtime'],
                    'summary' => $episodeData['summary'],
                    'image' => $episodeData['image']['original'] ?? null,
                ]
            );

            $processedEpisodes++;
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create/update episode: '.$e->getMessage(), ['episode_id' => $episodeData['id']]);
        }
    }
}
