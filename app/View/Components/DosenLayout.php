<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DosenLayout extends Component
{
    public function render(): View
    {
        return view('layouts.dosen');
    }
}
