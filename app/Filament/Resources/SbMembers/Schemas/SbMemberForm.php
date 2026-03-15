<?php

namespace App\Filament\Resources\SbMembers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SbMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                Toggle::make('speicherungok')
                    ->required(),
                Toggle::make('aktiv')
                    ->required(),
                TextInput::make('email_adfc')
                    ->email()
                    ->endsWith("@adfc-muenchen.de")
                    ->validationMessages([
                        "ends_with" => "Die E-Mail-Adresse muss auf @adfc-muenchen.de enden."
                    ])
                    ->default(null),
                TextInput::make('email_private')
                    ->email()
                    ->doesntEndWith("@adfc-muenchen.de")
                    ->validationMessages([
                        "doesnt_end_with" => "Bitte keine @adfc-muenchen.de Adresse hier eingeben."
                    ])
                    ->default(null),
                TextInput::make('phone_primary')
                    ->tel()
                    ->default(null),
                TextInput::make('phone_secondary')
                    ->tel()
                    ->default(null),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('adfc_id')
                    ->default(null),
                TextInput::make('gender')
                    ->default(null),
                Textarea::make('interests')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('birthday')
                    ->default(null),
                TextInput::make('admin_comments')
                    ->default(null),
                DateTimePicker::make('eingetragen'),
            ]);
    }
}
