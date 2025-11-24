<?php

namespace App\Filament\Resources\TransaksiKeluarResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
class Stats extends ChartWidget
{
    protected static ?string $heading = 'Transaksi Keluar per Bulan';

    protected function getData(): array
    {
        $data = DB::table('transaksi_keluar')
            ->selectRaw("
                DATE_FORMAT(tanggal_keluar, '%Y-%m') as bulan_key,
                DATE_FORMAT(tanggal_keluar, '%M %Y') as bulan_label,
                SUM(jumlah_keluar) as total
            ")
            ->groupBy('bulan_key', 'bulan_label')
            ->orderBy('bulan_key')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Keluar',
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
