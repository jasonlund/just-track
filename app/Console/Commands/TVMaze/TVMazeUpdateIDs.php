<?php

namespace App\Console\Commands\TVMaze;

use App\Models\Show;
use App\Services\TVMazeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TVMazeUpdateIDs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tv-maze:update-ids {--dry-run : Do not actually process. Used for testing. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the shows table given data from the TV Maze Show Index endpoint.';

    /**
     * The page size TV Maze uses.
     * Does not necessarily mean every page contains 250 entries, just that the show id is between 0-249, 250-499 etc.
     *
     * @see https://www.tvmaze.com/api#show-index
     *
     * @var int
     */
    private $pageSize = 250;

    /**
     * Execute the console command.
     */
    public function handle(TVMazeService $service)
    {
        if ($this->option('dry-run')) {
            return 0;
        }

        $count = 0;
        $pageCount = 0;

        $latestId = Show::latest('external_id')->first()->external_id ?? 0;
        $page = intval(floor($latestId / $this->pageSize));

        $this->info('Starting update with latest ID '.$latestId.' and page '.$page);

        while ($data = $service->shows($page)) {
            $pageItemCount = 0;

            foreach ($data as $show) {
                // Skip the entry if we already have it.
                if ($show['id'] <= $latestId) {
                    continue;
                }

                Show::create([
                    'external_id' => $show['id'],
                    'name' => $show['name'],
                    'type' => strtolower($show['type']),
                    'language' => strtolower($show['language']),
                    'status' => strtolower($show['status']),
                    'runtime' => $show['runtime'],
                    'average_runtime' => $show['averageRuntime'],
                    'premiered' => $show['premiered'],
                    'ended' => $show['ended'],
                    'tvdb_id' => $show['externals']['thetvdb'] ?? null,
                    'imdb_id' => $show['externals']['imdb'] ?? null,
                    'image' => $show['image']['original'] ?? null,
                    'summary' => $show['summary'],
                    'external_updated_at' => Carbon::parse($show['updated']),
                ]);

                $pageItemCount++;
            }

            $this->line('Processed '.$pageItemCount.' entries from page '.$page);

            $page++;
            $pageCount++;
            $count += $pageItemCount;
        }

        $this->info('Processed '.$count.' entries from '.$pageCount.' pages');

        return 0;
    }
}
