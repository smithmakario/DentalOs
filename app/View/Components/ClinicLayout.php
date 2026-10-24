<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ClinicLayout extends Component
{
    public function render(): View
    {
        return view('layouts.clinic');
    }
}
