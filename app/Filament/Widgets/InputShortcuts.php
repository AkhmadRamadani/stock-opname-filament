<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\TransaksiMasukResource;
use App\Filament\Resources\TransaksiKeluarResource;

class InputShortcuts extends Widget
{
    protected static string $view = 'filament.widgets.input-shortcuts';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Admin Input');
    }

    public function getMasukUrl(): string
    {
        return TransaksiMasukResource::getUrl('create');
    }

    public function getKeluarUrl(): string
    {
        return TransaksiKeluarResource::getUrl('create');
    }
}
