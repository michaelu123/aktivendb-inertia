<?php

namespace App\Filament\Resources\SbMembers;

// use App\Filament\Resources\SbMembers\Pages\CreateSbMember;
// use App\Filament\Resources\SbMembers\Pages\EditSbMember;
use App\Filament\Resources\SbMembers\Pages\ListSbMembers;
use App\Filament\Resources\SbMembers\Schemas\SbMemberForm;
use App\Filament\Resources\SbMembers\Tables\SbMembersTable;
use App\Models\SbMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SbMemberResource extends Resource
{
    protected static ?string $model = SbMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = "Serienbrief";
    protected static ?string $pluralModelLabel = 'Serienbrief-Antworten';
    protected static ?string $recordTitleAttribute = 'last_name';
    public static function form(Schema $schema): Schema
    {
        return SbMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SbMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSbMembers::route('/'),
            // 'create' => CreateSbMember::route('/create'),
            // 'edit' => EditSbMember::route('/{record}/edit'),
        ];
    }
}
