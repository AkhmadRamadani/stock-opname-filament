<?php

namespace App\Filament\Resources\VerifikasiLogResource\Pages;

use App\Filament\Resources\VerifikasiLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVerifikasiLog extends EditRecord
{
    protected static string $resource = VerifikasiLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
