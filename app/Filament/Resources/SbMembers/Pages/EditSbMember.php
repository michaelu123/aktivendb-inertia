<?php

namespace App\Filament\Resources\SbMembers\Pages;

use App\Filament\Resources\SbMembers\SbMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSbMember extends EditRecord
{
    protected static string $resource = SbMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
