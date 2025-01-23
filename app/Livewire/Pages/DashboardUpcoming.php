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
        return Episode::whereHas('season', function(Builder $query) {
            $query->whereIn('show_id', auth()->user()->shows->pluck('id'));
        })
            ->with('season', 'season.show')
            ->whereNotNull('air_date')
            ->where('air_date', '>=', now()->subHours(2))
            ->orderBy('air_date')
            ->get()
            ->groupBy(function($item) {
                $diff = now()->diffInDays($item->air_date);
                if($diff < 7) {
                    return 'd' . $item->air_date->format('Y-m-d');
                }else if($diff < 28) {
                    return 'w '. $item->air_date->startOfWeek()->format('Y-m-d');
                } else {
                    return 'm '. $item->air_date->startOfMonth()->format('Y-m-d');
                }
            });
    }
}
