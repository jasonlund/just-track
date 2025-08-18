<?php

namespace App\Livewire\Components\Layouts\App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Nav extends Component
{
    /**
     * Navigation items for both desktop and mobile menus
     *
     * @var array<int, array{
     *     route: string,        // Laravel route name (e.g., 'dashboard', 'show.index')
     *     label: string,        // Display text for the navigation item
     *     icon: string,         // Flux icon name for mobile sidebar (e.g., 'home', 'tv')
     *     current?: string      // Optional route pattern for active state (defaults to route value)
     * }>
     */
    public array $items = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'dashboard.upcoming', 'label' => 'Upcoming', 'icon' => 'calendar'],
        ['route' => 'dashboard.previously-aired', 'label' => 'Previously Aired', 'icon' => 'clock'],
        ['route' => 'search', 'label' => 'Search', 'icon' => 'magnifying-glass', 'current' => 'search*'],
        ['route' => 'show.index', 'label' => 'Shows', 'icon' => 'tv', 'current' => 'show.*'],
    ];

    public function logout(): void
    {
        $this->_logout();

        $this->redirect('/login', navigate: true);
    }

    private function _logout()
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }

    public function render()
    {
        return view('livewire.components.layouts.app.nav');
    }
}
