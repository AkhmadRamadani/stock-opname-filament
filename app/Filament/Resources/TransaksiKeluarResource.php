<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiKeluarResource\Pages;
use App\Models\TransaksiKeluar;
use App\Models\Barang;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class TransaksiKeluarResource extends Resource
{
    protected static ?string $model = TransaksiKeluar::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationLabel = 'Transaksi Keluar';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_keluar')
                            ->label('Tanggal Keluar')
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
                        Forms\Components\TextInput::make('jumlah_keluar')
                            ->label('Jumlah Keluar')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->step(1),
                        Forms\Components\TextInput::make('tujuan')
                            ->label('Tujuan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Departemen, divisi, atau tujuan penggunaan'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
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
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->live(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('id_user_verifikator')
                            ->label('Verifikator')
                            ->options(User::where('role', 'supervisor')->pluck('name', 'id'))
                            ->visible(fn(Get $get) => in_array($get('status'), ['verified', 'rejected'])),
                        Forms\Components\DateTimePicker::make('tanggal_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->visible(fn(Get $get) => in_array($get('status'), ['verified', 'rejected']))
                            ->default(now())
                            ->native(false),
                    ])
                    ->columns(2)
                    ->visible(fn(Get $get) => in_array($get('status'), ['verified', 'rejected'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_keluar')
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
                Tables\Columns\TextColumn::make('jumlah_keluar')
                    ->label('Jumlah')
                    ->numeric()
                    ->suffix(fn($record) => ' ' . $record->barang->satuan)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->limit(20),
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
                Tables\Filters\Filter::make('tanggal_keluar')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn($q) => $q->whereDate('tanggal_keluar', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn($q) => $q->whereDate('tanggal_keluar', '<=', $data['sampai_tanggal']));
                    }),
                Tables\Filters\SelectFilter::make('tujuan')
                    ->options(
                        TransaksiKeluar::distinct()
                            ->pluck('tujuan', 'tujuan')
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
                            'tipe_transaksi' => 'keluar',
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
            ])
            ->defaultSort('tanggal_keluar', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiKeluars::route('/'),
            'create' => Pages\CreateTransaksiKeluar::route('/create'),
            'edit' => Pages\EditTransaksiKeluar::route('/{record}/edit'),
        ];
    }
}
