<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\ResearchPaper;
use App\Services\ChainRegistry;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
    $chain = app(ChainRegistry::class);
    ResearchPaper::where('chain_status', ResearchPaper::STATUS_REGISTERED)
        ->orderBy('id', 'desc')
        ->limit(25)
        ->get()
        ->each(fn($p) => $chain->confirm($p));
})->everyTwoMinutes();