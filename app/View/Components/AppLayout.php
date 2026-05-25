<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Declare `title` as a public prop so `<x-app-layout title="X">` makes
     * `$title` available inside layouts.app.
     */
    public function __construct(public string $title = 'My Account') {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
