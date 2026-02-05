<?php

namespace App\Filament\Widgets;

use App\Models\VerifikasiLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class VerificationStatsChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Verifikasi';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Supervisor');
    }

    protected function getData(): array
    {
        $verified = VerifikasiLog::where('status_sesudah', 'verified')->count();
        $rejected = VerifikasiLog::where('status_sesudah', 'rejected')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Verifikasi',
                    'data' => [$verified, $rejected],
                    'backgroundColor' => ['#22c55e', '#ef4444'], // Green, Red
                ],
            ],
            'labels' => ['Approved', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
