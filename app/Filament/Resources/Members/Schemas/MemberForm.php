<?php

namespace App\Filament\Resources\Members\Schemas;

// use Filament\Forms\Components\DatePicker;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('name')
                //     ->disabled(true),
                // TextInput::make('email_adfc')
                //     ->email()
                //     ->disabled(true),
                // TextInput::make('email_private')
                //     ->email()
                //     ->disabled(true),
                // Toggle::make('responded_to_questionaire'),
                // DatePicker::make('responded_to_questionaire_at'),
            ]);
    }
}
