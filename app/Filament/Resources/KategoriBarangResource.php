<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriBarangResource\Pages;
use App\Models\KategoriBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class KategoriBarangResource extends Resource
{
    protected static ?string $model = KategoriBarang::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori Barang';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('kode_kategori')
                            ->label('Kode Kategori')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Contoh: KAT001'),
                        Forms\Components\TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Elektronik'),
                        Forms\Components\Textarea::make('deskripsi_kategori')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Deskripsi singkat kategori barang'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Kategori aktif dapat digunakan untuk barang baru'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_kategori')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deskripsi_kategori')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('barang_count')
                    ->label('Jumlah Barang')
                    ->counts('barang')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin menghapus kategori ini? Data yang terkait mungkin akan terpengaruh.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Kategori')
                    ->schema([
                        Infolists\Components\TextEntry::make('kode_kategori')
                            ->label('Kode Kategori'),
                        Infolists\Components\TextEntry::make('nama_kategori')
                            ->label('Nama Kategori'),
                        Infolists\Components\TextEntry::make('deskripsi_kategori')
                            ->label('Deskripsi'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d F Y, H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKategoriBarangs::route('/'),
            'create' => Pages\CreateKategoriBarang::route('/create'),
            'view' => Pages\ViewKategoriBarang::route('/{record}'),
            'edit' => Pages\EditKategoriBarang::route('/{record}/edit'),
        ];
    }
}
