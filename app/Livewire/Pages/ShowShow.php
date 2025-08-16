<?php

namespace App\Livewire\Pages;

use App\Models\Show;
use App\Services\ImageService;
use App\Services\SeasonService;
use App\Services\TVMazeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowShow extends Component
{
    #[Locked]
    public Show $show;

    private $tvMazeService;

    private $seasonService;

    private $imageService;

    public function boot(TVMazeService $tvMazeService, SeasonService $seasonService, ImageService $imageService)
    {
        $this->tvMazeService = $tvMazeService;
        $this->seasonService = $seasonService;
        $this->imageService = $imageService;
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

                $this->imageService->fetchAndStoreShowImages($show);

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
        return Show::with(['images' => function ($query) {
            $query->where('type', \App\Enums\ImageType::HD_TV_LOGO->value)
                ->mostPopular();
        }])->find($this->show->id);
    }
}
