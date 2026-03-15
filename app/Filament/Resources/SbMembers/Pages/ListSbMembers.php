<?php

namespace App\Filament\Resources\SbMembers\Pages;

use App\Filament\Resources\SbMembers\SbMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSbMembers extends ListRecords
{
    protected static string $resource = SbMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
