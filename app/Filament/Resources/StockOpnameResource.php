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

    protected static ?int $navigationSort = 4;

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

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'verified',
                        'success' => 'published',
                    ])
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
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $tanggal = $data['tanggal'];

                        return Excel::download(
                            new StockOpnameExport($tanggal),
                            "Stock_Opname_" . date('d-m-Y', strtotime($tanggal)) . ".xlsx"
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
            //
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
