<?php

namespace App\Console\Commands\TMDB;

use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class UpdateIDs extends Command
{
    /**
     * Date format used in file names.
     *
     * @var string
     */
    private $dateFormat = 'm-d-Y';

    private $url = 'http://files.tmdb.org/p/exports/tv_series_ids_{date}.json.gz';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmdb:update-ids {--date= : The date to export in the format m-d-Y. If undefined, then today.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the shows table given data from he daily TMDB ID export.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '2048M');

        $today = $this->option('date') ? Carbon::createFromFormat($this->dateFormat, $this->option('date')) : now();

        // TMDB updates this file every day starting at 7 AM and they will be available by 8 AM.
        // TMDB deletes files older than 3 months.
        // Do not allow if the date is today before 8 AM, in the future or before 3 months ago.
        //
        // @link https://developer.themoviedb.org/docs/daily-id-exports#availability
        if($today->isToday() && now()->lt(now()->setTime(8, 0))) {
            $this->fail('Cannot run command before 8 AM today, ' . now()->format($this->dateFormat) . '.');
        }else if(! $today->isToday()) {
            if($today->gt(now()->addDay()->startOfDay())) {
                $this->fail('Cannot run command for dates after today, ' . now()->format($this->dateFormat) . '.');
            }else if($today->lt(now()->subMonths(3)->startOfDay())) {
                $this->fail('Cannot run command for dates before 3 months ago, ' . now()->subMonths(3)->format($this->dateFormat) . '.');
            }
        }

        // TODO -- test coverage for ingestion.
        if(app()->runningUnitTests()) {
            return 0;
        }

        $formattedDate = str_replace('-', '_', $today->format($this->dateFormat));
        $filePath = 'temp/tmdb/series-' . $formattedDate . '.json';
        Storage::put(
            $filePath,
            gzdecode(file_get_contents(str_replace('{date}', $formattedDate, $this->url)))
        );

        $filePath = storage_path('app/' . $filePath);
        $count = intval(exec("wc -l '$filePath'"));

        $bar = $this->output->createProgressBar($count);

        $bar->start();

        $handle = fopen(storage_path('app/temp/tmdb/series-' . $formattedDate . '.json'), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $data = json_decode($line, 1);
                Show::updateOrCreate(
                    ['external_id' => $data['id']],
                    ['original_name' => $data['original_name']]);
                $bar->advance();
            }

            fclose($handle);
        }

        $bar->finish();

        Storage::delete($filePath);
    }
}
