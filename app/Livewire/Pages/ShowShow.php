<?php

namespace App\Livewire\Pages;

use App\Models\Season;
use App\Models\Show;
use App\Services\TMDBService;
use App\Services\TVMazeService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;

    private $service;

    public function boot(TVMazeService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show, $attach = false)
    {
        // If we haven't initialized the show's seasons yet, do so first.
        // It would be better to do this in the TVMazeUpdateIDs command or the EpisodeList component, but the endpoints
        // called for both of those do not support embedding, so this becomes the most efficient way to get a show's
        // seasons that we don't already have.
        if ($show->seasons()->count() === 0) {
            $data = $this->service->show($show->external_id);

            DB::beginTransaction();

            try {
                foreach ($data['_embedded']['seasons'] as $season) {
                    Season::create([
                        'show_id' => $show->id,
                        'external_id' => $season['id'],
                        'number' => $season['number'],
                        'name' => $season['name'],
                        'image' => $season['image']['original'] ?? null,
                    ]);
                }

                DB::commit();
            }catch (\Exception $e) {
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
