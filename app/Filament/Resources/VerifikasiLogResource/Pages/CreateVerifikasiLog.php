<?php

namespace App\Filament\Resources\VerifikasiLogResource\Pages;

use App\Filament\Resources\VerifikasiLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVerifikasiLog extends CreateRecord
{
    protected static string $resource = VerifikasiLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
