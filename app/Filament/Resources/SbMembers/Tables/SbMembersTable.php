<?php

namespace App\Filament\Resources\SbMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class SbMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eingetragen')
                    ->label("Übernahme")
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label("Vorname")
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label("Nachname")
                    ->sortable()
                    ->searchable(),
                IconColumn::make('speicherungok')
                    ->label("Zustimmung")
                    ->sortable()
                    ->boolean(),
                IconColumn::make('aktiv')
                    ->label("Aktiv")
                    ->sortable()
                    ->boolean(),
                TextColumn::make('email_adfc')
                    ->label("ADFC Email")
                    ->searchable(),
                TextColumn::make('email_private')
                    ->label("Private Email")
                    ->searchable(),
                TextColumn::make('phone_primary')
                    ->label("Telefon 1")
                    ->searchable(),
                TextColumn::make('phone_secondary')
                    ->label("Telefon 2")
                    ->searchable(),
                TextColumn::make('address')
                    ->label("Postleitzahl")
                    ->searchable(),
                TextColumn::make('adfc_id')
                    ->label("ADFC-Mitgliedsnummer")
                    ->searchable(),
                TextColumn::make('gender')
                    ->label("Geschlecht")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('birthday')
                    ->label("Geburtsjahr")
                    ->sortable()
                    ->searchable(),
                TextInputColumn::make('admin_comments')
                    ->label("Admin-Bemerkung")
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label("Eingang")
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                // ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label("")
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('eingetragen', 'asc');
    }
}
