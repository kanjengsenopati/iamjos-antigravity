<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public $title;
    public $journal;
    public $journalSlug;

    public function __construct($title = null, $journal = null, $journalSlug = null)
    {
        $this->title = $title;
        $this->journal = $journal;
        $this->journalSlug = $journalSlug;
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
