<?php

namespace App\Filament\Resources\VerifikasiLogResource\Pages;

use App\Filament\Resources\VerifikasiLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVerifikasiLogs extends ListRecords
{
    protected static string $resource = VerifikasiLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
