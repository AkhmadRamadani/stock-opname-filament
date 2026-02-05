<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerifikasiLogResource\Pages;
use App\Models\VerifikasiLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class VerifikasiLogResource extends Resource
{
    protected static ?string $model = VerifikasiLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Verifikasi Log';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('tipe_transaksi')
                            ->label('Tipe Transaksi')
                            ->required()
                            ->options([
                                'masuk' => 'Transaksi Masuk',
                                'keluar' => 'Transaksi Keluar',
                                'laporan' => 'Laporan Stok',
                            ]),
                        Forms\Components\TextInput::make('id_referensi')
                            ->label('ID Referensi')
                            ->required()
                            ->numeric()
                            ->helperText('ID dari record yang diverifikasi'),
                        Forms\Components\Select::make('id_user_verifikator')
                            ->label('Verifikator')
                            ->required()
                            ->default(auth()->id())
                            ->options(User::where('role', 'supervisor')->pluck('name', 'id')),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status_sebelum')
                            ->label('Status Sebelum')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'draft' => 'Draft',
                                'verified' => 'Verified',
                            ])
                            ->default('pending'),
                        Forms\Components\Select::make('status_sesudah')
                            ->label('Status Sesudah')
                            ->required()
                            ->options([
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'published' => 'Published',
                            ]),
                        Forms\Components\DateTimePicker::make('tanggal_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->required()
                            ->default(now())
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Masukkan catatan atau alasan verifikasi...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_verifikasi')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tipe_transaksi')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'masuk',
                        'warning' => 'keluar',
                        'info' => 'laporan',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'masuk' => 'Transaksi Masuk',
                        'keluar' => 'Transaksi Keluar',
                        'laporan' => 'Laporan Stok',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('id_referensi')
                    ->label('ID Ref')
                    ->sortable(),
                Tables\Columns\TextColumn::make('userVerifikator.name')
                    ->label('Verifikator')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\BadgeColumn::make('status_sebelum')
                    ->label('Status Awal')
                    ->colors([
                        'warning' => 'pending',
                        'gray' => 'draft',
                        'success' => 'verified',
                    ]),
                Tables\Columns\BadgeColumn::make('status_sesudah')
                    ->label('Status Akhir')
                    ->colors([
                        'success' => 'verified',
                        'danger' => 'rejected',
                        'info' => 'published',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'verified',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-eye' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->options([
                        'masuk' => 'Transaksi Masuk',
                        'keluar' => 'Transaksi Keluar',
                        'laporan' => 'Laporan Stok',
                    ]),
                Tables\Filters\SelectFilter::make('status_sesudah')
                    ->label('Status Akhir')
                    ->options([
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'published' => 'Published',
                    ]),
                Tables\Filters\SelectFilter::make('id_user_verifikator')
                    ->label('Verifikator')
                    ->options(User::where('role', 'supervisor')->pluck('name', 'id')),
                Tables\Filters\Filter::make('tanggal_verifikasi')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn($q) => $q->whereDate('tanggal_verifikasi', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn($q) => $q->whereDate('tanggal_verifikasi', '<=', $data['sampai_tanggal']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(), // Audit logs should ideally be read-only
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn() => auth()->user()->role === 'admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->role === 'admin'),
                ]),
            ])
            ->defaultSort('tanggal_verifikasi', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Verifikasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('tipe_transaksi')
                            ->label('Tipe Transaksi')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'masuk' => 'Transaksi Masuk',
                                'keluar' => 'Transaksi Keluar',
                                'laporan' => 'Laporan Stok',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'masuk' => 'success',
                                'keluar' => 'warning',
                                'laporan' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('id_referensi')
                            ->label('ID Referensi'),
                        Infolists\Components\TextEntry::make('userVerifikator.name')
                            ->label('Verifikator'),
                        Infolists\Components\TextEntry::make('tanggal_verifikasi')
                            ->label('Tanggal Verifikasi')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Perubahan Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status_sebelum')
                            ->label('Status Sebelum')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'draft' => 'gray',
                                'verified' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('status_sesudah')
                            ->label('Status Sesudah')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'verified' => 'success',
                                'rejected' => 'danger',
                                'published' => 'info',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Tidak ada catatan'),
                    ])
                    ->visible(fn($record) => !empty($record->catatan_verifikasi)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerifikasiLogs::route('/'),
            'create' => Pages\CreateVerifikasiLog::route('/create'),
            'view' => Pages\ViewVerifikasiLog::route('/{record}'),
            // 'edit' => Pages\EditVerifikasiLog::route('/{record}/edit'), // Disabled edit for logs
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('tanggal_verifikasi', today())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
