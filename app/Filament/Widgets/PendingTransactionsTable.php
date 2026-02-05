<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiMasuk;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PendingTransactionsTable extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Transaksi Masuk Pending Verifikasi';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Supervisor');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransaksiMasuk::query()->where('status', 'pending')
            )
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('jumlah_masuk')
                    ->label('Jumlah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('userInput.name')
                    ->label('Input By')
                    ->description(fn (TransaksiMasuk $record) => $record->created_at->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-m-eye')
                    ->url(fn (TransaksiMasuk $record): string => \App\Filament\Resources\TransaksiMasukResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
