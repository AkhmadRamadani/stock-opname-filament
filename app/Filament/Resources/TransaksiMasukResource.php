<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiMasukResource\Pages;
use App\Models\TransaksiMasuk;
use App\Models\Barang;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class TransaksiMasukResource extends Resource
{
    protected static ?string $model = TransaksiMasuk::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Transaksi Masuk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required()
                            ->default(today())
                            ->native(false),
                        Forms\Components\Select::make('kode_barang')
                            ->label('Barang')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return Barang::with('kategori')
                                    ->get()
                                    ->mapWithKeys(function ($barang) {
                                        return [$barang->kode_barang => "{$barang->kode_barang} - {$barang->nama_barang} ({$barang->kategori->nama_kategori})"];
                                    });
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    $barang = Barang::where('kode_barang', $state)->first();
                                    $set('satuan_display', $barang?->satuan ?? '');
                                    $set('harga_beli', $barang?->harga_satuan ?? 0);
                                }
                            }),
                        Forms\Components\TextInput::make('satuan_display')
                            ->label('Satuan')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_masuk')
                            ->label('Jumlah Masuk')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $harga_beli = $get('harga_beli') ?? 0;
                                $set('total_harga_display', $state * $harga_beli);
                            }),
                        Forms\Components\TextInput::make('harga_beli')
                            ->label('Harga Beli (per unit)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $jumlah_masuk = $get('jumlah_masuk') ?? 0;
                                $set('total_harga_display', $jumlah_masuk * $state);
                            }),
                        Forms\Components\TextInput::make('total_harga_display')
                            ->label('Total Harga')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Rp')
                            ->numeric(),
                        Forms\Components\TextInput::make('supplier')
                            ->label('Supplier')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama supplier atau toko'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Catatan tambahan tentang transaksi'),
                        Forms\Components\Select::make('id_user_input')
                            ->label('User Input')
                            ->required()
                            ->default(auth()->id())
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pending')
                            ->disabled(fn () => auth()->user()->hasRole('Admin Input'))
                            ->dehydrated()
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->live(),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('jumlah_masuk')
                    ->label('Jumlah')
                    ->numeric()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Supplier')
                    ->limit(20)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('userInput.name')
                    ->label('Input By')
                    ->limit(15)
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'verified',
                        'heroicon-o-x-circle' => 'rejected',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('tanggal_masuk')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn($q) => $q->whereDate('tanggal_masuk', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn($q) => $q->whereDate('tanggal_masuk', '<=', $data['sampai_tanggal']));
                    }),
                Tables\Filters\SelectFilter::make('supplier')
                    ->options(
                        TransaksiMasuk::distinct()
                            ->pluck('supplier', 'supplier')
                            ->filter()
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending' && auth()->user()->role === 'supervisor')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status Verifikasi')
                            ->options([
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'status' => $data['status'],
                            'id_user_verifikator' => auth()->id(),
                            'tanggal_verifikasi' => now(),
                        ]);

                        // Create verification log
                        \App\Models\VerifikasiLog::create([
                            'tipe_transaksi' => 'masuk',
                            'id_referensi' => $record->id,
                            'id_user_verifikator' => auth()->id(),
                            'status_sebelum' => 'pending',
                            'status_sesudah' => $data['status'],
                            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                            'tanggal_verifikasi' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
            ])
            ->defaultSort('tanggal_masuk', 'desc');
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
            'index' => Pages\ListTransaksiMasuks::route('/'),
            'create' => Pages\CreateTransaksiMasuk::route('/create'),
            'edit' => Pages\EditTransaksiMasuk::route('/{record}/edit'),
        ];
    }
}
