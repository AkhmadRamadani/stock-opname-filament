<?php

namespace App\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VerifikasiLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifikasiLogs';

    protected static ?string $title = 'Riwayat Verifikasi';

    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status_sebelum')
                    ->label('Status Sebelum')
                    ->disabled(),
                Forms\Components\TextInput::make('status_sesudah')
                    ->label('Status Sesudah')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('tanggal_verifikasi')
                    ->label('Tanggal')
                    ->disabled(),
                Forms\Components\Textarea::make('catatan_verifikasi')
                    ->label('Catatan Verifikasi')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_verifikasi')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('userVerifikator.name')
                    ->label('Verifikator'),
                Tables\Columns\TextColumn::make('status_sebelum')
                    ->label('Dari Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'draft' => 'gray',
                        'verified' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status_sesudah')
                    ->label('Ke Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'draft' => 'gray',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'published' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(fn($state) => $state),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('tanggal_verifikasi', 'desc');
    }
}
