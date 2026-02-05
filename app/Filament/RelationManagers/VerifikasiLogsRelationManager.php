<?php

namespace App\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VerifikasiLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifikasiLogs';

    protected static ?string $title = 'Riwayat Verifikasi';

    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status_sesudah')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\BadgeColumn::make('status_sebelum')
                    ->label('Status Awal')
                    ->colors([
                        'warning' => 'pending',
                        'gray' => 'draft',
                        'success' => 'verified',
                        'danger' => 'rejected',
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
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('tanggal_verifikasi', 'desc');
    }
}
