<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyTransactionsChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi Bulanan';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Super Admin');
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $labels = $months->map(function ($m) {
            return Carbon::createFromFormat('Y-m', $m)->format('M Y');
        })->toArray();

        // SQLite syntax for date formatting
        $masukCounts = TransaksiMasuk::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("COUNT(*) as total")
        )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        $keluarCounts = TransaksiKeluar::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("COUNT(*) as total")
        )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        $finalMasuk = $months->map(fn($m) => $masukCounts[$m] ?? 0)->toArray();
        $finalKeluar = $months->map(fn($m) => $keluarCounts[$m] ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi Masuk',
                    'data' => $finalMasuk,
                    'borderColor' => '#3b82f6', // Blue
                ],
                [
                    'label' => 'Transaksi Keluar',
                    'data' => $finalKeluar,
                    'borderColor' => '#ef4444', // Red
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
