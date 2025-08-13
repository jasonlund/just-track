<?php

namespace App\Livewire\Pages;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Upcoming Shows')]
class DashboardUpcoming extends Component
{
    #[Computed]
    public function episodes()
    {
        return Episode::whereHas('season', function (Builder $query) {
            $query->whereIn('show_id', auth()->user()->shows->pluck('id'));
        })
            ->with('season', 'season.show')
            ->whereNotNull('air_timestamp')
            ->where('air_timestamp', '>=', now()->subHours(2))
            ->orderBy('air_timestamp')
            ->get()
            ->groupBy(function ($item) {
                $diff = now()->diffInDays($item->air_timestamp);
                if ($diff < 7) {
                    return 'd'.$item->air_timestamp->format('Y-m-d');
                } elseif ($diff < 28) {
                    return 'w '.$item->air_timestamp->startOfWeek()->format('Y-m-d');
                } else {
                    return 'm '.$item->air_timestamp->startOfMonth()->format('Y-m-d');
                }
            });
    }
}
