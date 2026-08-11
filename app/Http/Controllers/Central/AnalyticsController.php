<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\PlatformAnalyticsService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        private PlatformAnalyticsService $platformAnalyticsService,
    ) {}

    public function index(): View
    {
        $metrics = $this->platformAnalyticsService->forUser(auth()->user());

        return view('central.analytics.index', $metrics);
    }

    public function export(): StreamedResponse
    {
        return $this->platformAnalyticsService->exportCsvForUser(auth()->user());
    }
}
