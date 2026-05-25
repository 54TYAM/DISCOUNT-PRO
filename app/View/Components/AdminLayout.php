<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Declare `title` as a public prop so `<x-admin-layout title="X">` makes
     * `$title` available inside layouts.admin (otherwise the attribute is
     * silently absorbed into $attributes and never reaches the view).
     */
    public function __construct(public string $title = 'Dashboard') {}

    public function render(): View
    {
        return view('layouts.admin');
    }
}
