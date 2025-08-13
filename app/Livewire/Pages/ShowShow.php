<?php

namespace App\Livewire\Pages;

use App\Models\Show;
use App\Services\SeasonService;
use App\Services\TVMazeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;

    private $tvMazeService;

    private $seasonService;

    public function boot(TVMazeService $tvMazeService, SeasonService $seasonService)
    {
        $this->tvMazeService = $tvMazeService;
        $this->seasonService = $seasonService;
    }

    public function mount(Show $show, $attach = false)
    {
        // If we haven't initialized the show yet, do so first.
        // It would be better to do this in the TVMazeUpdateIDs command or the EpisodeList component, but the endpoints
        // called for both of those do not support embedding, so this becomes the most efficient way to get a show's
        // seasons that we don't already have.
        if (! $show->initialized) {
            $data = $this->tvMazeService->show($show->external_id);

            DB::beginTransaction();

            try {
                foreach ($data['_embedded']['seasons'] as $seasonData) {
                    $this->seasonService->create($show, $seasonData);
                }

                // Mark the show as initialized
                $show->initialized = true;
                $show->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
            }
        }

        if ($attach !== false) {
            Auth::user()->shows()->syncWithoutDetaching([$show->id]);
            // Clear our parameter
            $this->redirectIntended(route('show.show', $show), navigate: true);
        }

        $this->show = $show;
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            Loading...
        </div>
        HTML;
    }

    #[Computed]
    public function show()
    {
        return Show::find($this->show->id);
    }
}
