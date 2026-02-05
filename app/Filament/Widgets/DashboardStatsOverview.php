<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\TransaksiKeluar;
use App\Models\TransaksiMasuk;
use App\Models\VerifikasiLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $stats = [];

        // Role: Super Admin (Admin)
        if ($user->hasRole('Super Admin')) {
            $pendingCount = TransaksiMasuk::where('status', 'pending')->count() +
                            TransaksiKeluar::where('status', 'pending')->count();

            $stats[] = Stat::make('Total Barang', Barang::count())
                ->description('Barang terdaftar')
                ->icon('heroicon-o-cube');

            $stats[] = Stat::make('Total Kategori', KategoriBarang::count())
                ->description('Kategori aktif')
                ->icon('heroicon-o-tag');

            $stats[] = Stat::make('Transaksi Pending', $pendingCount)
                ->description('Menunggu verifikasi')
                ->color('warning')
                ->icon('heroicon-o-clock');
        }

        // Role: Admin Input
        if ($user->hasRole('Admin Input')) {
            $todayInput = TransaksiMasuk::where('id_user_input', $user->id)->whereDate('created_at', today())->count() +
                          TransaksiKeluar::where('id_user_input', $user->id)->whereDate('created_at', today())->count();

            $myPending = TransaksiMasuk::where('id_user_input', $user->id)->where('status', 'pending')->count() +
                         TransaksiKeluar::where('id_user_input', $user->id)->where('status', 'pending')->count();

            $stats[] = Stat::make('Transaksi Hari Ini', $todayInput)
                ->description('Diinput hari ini')
                ->icon('heroicon-o-pencil-square');

            $stats[] = Stat::make('Transaksi Pending', $myPending)
                ->description('Belum diverifikasi')
                ->color('warning')
                ->icon('heroicon-o-clock');
        }

        // Role: Supervisor
        if ($user->hasRole('Supervisor')) {
            $verifiedToday = VerifikasiLog::where('id_user_verifikator', $user->id)
                ->whereDate('created_at', today())
                ->count();

            // Supervisor needs to see Global Pending to verify them
            $globalPending = TransaksiMasuk::where('status', 'pending')->count() +
                             TransaksiKeluar::where('status', 'pending')->count();

            $stats[] = Stat::make('Verified Today', $verifiedToday)
                ->description('Diverifikasi hari ini')
                ->color('success')
                ->icon('heroicon-o-check-badge');

            $stats[] = Stat::make('Pending Verifikasi', $globalPending)
                ->description('Menunggu verifikasi')
                ->color('warning')
                ->icon('heroicon-o-inbox');
        }

        return $stats;
    }
}
