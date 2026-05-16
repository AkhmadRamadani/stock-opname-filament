<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\Barang;
use App\Models\LaporanStok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameExport;
use Illuminate\Database\Eloquent\Builder;

class StockOpnameResource extends Resource
{
    protected static ?string $model = LaporanStok::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Stock Opname';

    protected static ?string $modelLabel = 'Stock Opname';

    protected static ?string $navigationGroup = 'Stock Opname';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Periode')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barang.kategori.nama_kategori')
                    ->label('Kategori')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_masuk')
                    ->label('Masuk')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_keluar')
                    ->label('Keluar')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Stok Akhir')
                    ->alignCenter()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'secondary',
                        'verified' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'verified' => 'Verified',
                        'published' => 'Published',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'draft' && auth()->user()->role === 'supervisor')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => 'verified',
                            'id_user_verifikator' => auth()->id(),
                            'tanggal_verifikasi' => now(),
                        ]);

                        \App\Models\VerifikasiLog::create([
                            'tipe_transaksi' => 'laporan',
                            'id_referensi' => $record->id,
                            'id_user_verifikator' => auth()->id(),
                            'status_sebelum' => 'draft',
                            'status_sesudah' => 'verified',
                            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                            'tanggal_verifikasi' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'verified' && (auth()->user()->role === 'manager' || auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor'))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Publish')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => 'published',
                            'id_user_verifikator' => auth()->id(),
                            'tanggal_verifikasi' => now(),
                        ]);

                        \App\Models\VerifikasiLog::create([
                            'tipe_transaksi' => 'laporan',
                            'id_referensi' => $record->id,
                            'id_user_verifikator' => auth()->id(),
                            'status_sebelum' => 'verified',
                            'status_sesudah' => 'published',
                            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                            'tanggal_verifikasi' => now(),
                        ]);
                    }),
            ])
            ->headerActions([
                Action::make('generateStockOpname')
                    ->label('Generate Stock Opname')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        self::generateStockOpname($data['tanggal']);

                        Notification::make()
                            ->title('Stock Opname Generated')
                            ->success()
                            ->send();
                    }),

                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $dari_tanggal = $data['dari_tanggal'];
                        $sampai_tanggal = $data['sampai_tanggal'];

                        return Excel::download(
                            new StockOpnameExport($dari_tanggal, $sampai_tanggal),
                            "Stock_Opname_" . date('d-m-Y', strtotime($dari_tanggal)) . "_sd_" . date('d-m-Y', strtotime($sampai_tanggal)) . ".xlsx"
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\VerifikasiLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'view' => Pages\ViewStockOpname::route('/{record}'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }

    protected static function generateStockOpname($tanggal)
    {
        $barangs = Barang::all();

        foreach ($barangs as $barang) {
            // Check if report already exists for this date
            $existing = LaporanStok::where('kode_barang', $barang->kode_barang)
                ->whereDate('tanggal', $tanggal)
                ->first();

            if ($existing) {
                continue; // Skip if already exists
            }

            // Get stok_awal from previous day
            $tanggalSebelumnya = date('Y-m-d', strtotime($tanggal . ' -1 day'));

            $laporanSebelumnya = LaporanStok::where('kode_barang', $barang->kode_barang)
                ->whereDate('tanggal', $tanggalSebelumnya)
                ->first();

            $stokAwal = $laporanSebelumnya->stok_akhir ?? 0;

            // Calculate total incoming transactions for this date
            $totalMasuk = DB::table('transaksi_masuk')
                ->where('kode_barang', $barang->kode_barang)
                ->where('status', 'verified')
                ->whereDate('tanggal_masuk', $tanggal)
                ->sum('jumlah_masuk');

            // Calculate total outgoing transactions for this date
            $totalKeluar = DB::table('transaksi_keluar')
                ->where('kode_barang', $barang->kode_barang)
                ->where('status', 'verified')
                ->whereDate('tanggal_keluar', $tanggal)
                ->sum('jumlah_keluar');

            // Calculate final stock
            $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;

            // Save to database
            LaporanStok::create([
                'kode_barang' => $barang->kode_barang,
                'tanggal' => $tanggal,
                'stok_awal' => $stokAwal,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'stok_akhir' => $stokAkhir,
                'status' => 'draft',
            ]);
        }
    }
}
