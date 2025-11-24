<?php

namespace App\Filament\Resources\TransaksiMasukResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
class Stats extends ChartWidget
{
    protected static ?string $heading = 'Transaksi Masuk per Bulan';

    protected function getData(): array
    {
        $data = DB::table('transaksi_masuk')
            ->selectRaw("
                DATE_FORMAT(tanggal_masuk, '%Y-%m') as bulan_key,
                DATE_FORMAT(tanggal_masuk, '%M %Y') as bulan_label,
                SUM(jumlah_masuk) as total
            ")
            ->groupBy('bulan_key', 'bulan_label')
            ->orderBy('bulan_key')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Masuk',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->pluck('bulan_label'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
