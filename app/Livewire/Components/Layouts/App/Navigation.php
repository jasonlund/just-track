<?php

namespace App\Livewire\Components\Layouts\App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Navigation extends Component
{
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
}
