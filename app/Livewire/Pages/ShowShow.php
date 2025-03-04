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
        // If we haven't initialized the show yet, do so first.
        if ($show->seasons()->count() === 0) {
            $data = $this->service->show($show->external_id);

            DB::beginTransaction();

            try {
                foreach ($data['_embedded']['seasons'] as $season) {
                    Season::create([
                        'show_id' => $show->id,
                        'external_id' => $season['id'],
                        'number' => $season['number'],
                        'premiere_date' => $season['premiereDate'],
                        'name' => $season['name'] === '' ? null : $season['name'],
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
