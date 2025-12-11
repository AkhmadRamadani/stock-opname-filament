<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanStokResource\Pages;
use App\Models\LaporanStok;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class LaporanStokResource extends Resource
{
    protected static ?string $model = LaporanStok::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Stok';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\Select::make('kode_barang')
                            ->label('Barang')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn() => Barang::with('kategori')->get()->pluck('nama_barang', 'kode_barang'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('stok_awal', 0);
                                    $set('stok_akhir', 0);
                                    return;
                                }

                                // Ambil stok akhir terakhir dari laporan stok barang ini
                                $lastReport = \App\Models\LaporanStok::where('kode_barang', $state)
                                    ->orderByDesc('tanggal')
                                    ->first();

                                $stokAwal = $lastReport?->stok_akhir ?? 0;

                                // Set stok awal
                                $set('stok_awal', $stokAwal);

                                // Hitung stok akhir langsung
                                $stokAkhir = (int)$stokAwal
                                    + (int)$get('total_masuk')
                                    - (int)$get('total_keluar');

                                $set('stok_akhir', $stokAkhir);
                            }),

                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal Laporan')
                            ->required()
                            ->default(today())
                            ->maxDate(today())
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Data Stok')
                    ->schema([
                        Forms\Components\TextInput::make('stok_awal')
                            ->label('Stok Awal')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                $set('stok_akhir', (int)$state + (int)$get('total_masuk') - (int)$get('total_keluar'))
                            ),

                        Forms\Components\TextInput::make('total_masuk')
                            ->label('Total Masuk')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                $set('stok_akhir', (int)$get('stok_awal') + (int)$state - (int)$get('total_keluar'))
                            ),

                        Forms\Components\TextInput::make('total_keluar')
                            ->label('Total Keluar')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                $set('stok_akhir', (int)$get('stok_awal') + (int)$get('total_masuk') - (int)$state)
                            ),

                        Forms\Components\TextInput::make('stok_akhir')
                            ->label('Stok Akhir')
                            ->default(0)
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                $set('stok_awal', (int)$get('stok_akhir') - (int)$get('total_masuk') + (int)$get('total_keluar'))
                            )->readOnly(),

                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('barang.kategori.nama_kategori')
                    ->label('Kategori')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->numeric()
                    ->alignEnd()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan),
                Tables\Columns\TextColumn::make('total_masuk')
                    ->label('Masuk')
                    ->numeric()
                    ->alignEnd()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan)
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_keluar')
                    ->label('Keluar')
                    ->numeric()
                    ->alignEnd()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan)
                    ->color('danger'),
                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Stok Akhir')
                    ->numeric()
                    ->alignEnd()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan)
                    ->weight('bold')
                    ->color(fn($record) => $record->stok_akhir < 10 ? 'danger' : ($record->stok_akhir < 50 ? 'warning' : 'success')),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn($q) => $q->whereDate('tanggal', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn($q) => $q->whereDate('tanggal', '<=', $data['sampai_tanggal']));
                    }),
                Tables\Filters\SelectFilter::make('barang')
                    ->relationship('barang', 'nama_barang')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('stok_rendah')
                    ->label('Stok Rendah (< 10)')
                    ->query(fn(Builder $query): Builder => $query->where('stok_akhir', '<', 10)),
                Tables\Filters\Filter::make('stok_menipis')
                    ->label('Stok Menipis (< 50)')
                    ->query(fn(Builder $query): Builder => $query->where('stok_akhir', '<', 50)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            // Implement Excel export logic here
                            // You can use maatwebsite/excel package
                        }),
                ]),
            ])
            ->defaultSort('tanggal', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanStoks::route('/'),
            'create' => Pages\CreateLaporanStok::route('/create'),
            'edit' => Pages\EditLaporanStok::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('stok_akhir', '<', 10)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
