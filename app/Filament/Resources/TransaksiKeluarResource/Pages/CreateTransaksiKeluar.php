<?php

namespace App\Filament\Resources\TransaksiKeluarResource\Pages;

use App\Filament\Resources\TransaksiKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksiKeluar extends CreateRecord
{
    protected static string $resource = TransaksiKeluarResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
