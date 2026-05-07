<?php

namespace App\Filament\Resources\SbMembers\Tables;

use App\Models\MemberTeamAktionen;
use App\Models\SbMember;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

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
                TextColumn::make('teams.name')
                    ->label("AGs/OGs")
                    ->color(function (SbMember $sbMember, $state) {
                        $teams = $sbMember->teams->filter(fn($team) => $team->name == $state);
                        $team = $teams->first();
                        $color = match ($team->team_sbmembers->aktion) {
                            "delete" => "danger",
                            "add" => "success",
                            default => "gray",
                        };
                        return $color;
                    })
                    ->badge()
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
                    ->label("updated_at")
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions(
                [
                    BulkActionGroup::make([
                        BulkAction::make('übernehmen1')
                            ->label("Selektierte übernehmen")
                            ->icon('heroicon-o-document-plus')
                            ->deselectRecordsAfterCompletion()
                            ->modalContent(function (Collection $sbMembers, BulkAction $action) {
                                $ids = $sbMembers->pluck('id')->toArray();
                                $memberTeamAktionen = new MemberTeamAktionen();
                                return new HtmlString(
                                    Blade::render('@livewire("⚡process-sb-members", [
                                            "recordIds" => $ids, 
                                            "id" => $modalId,
                                            "memberTeamAktionen" => $memberTeamAktionen
                                        ])',
                                        [
                                            'ids' => $ids,
                                            // if this is no longer ok, look at the modal in Chrome inspector, to see how it is formed.
                                            'modalId' => "fi-" . $action->getLivewire()->getId() . "-action-" . $action->getNestingIndex(),
                                            'memberTeamAktionen' => $memberTeamAktionen
                                        ]
                                    )
                                );
                            })
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false),
                        DeleteBulkAction::make(),
                    ]),
                    Action::make('übernehmen2')
                        ->label("Alle noch nicht übernommenen übernehmen")
                        ->icon('heroicon-o-document-plus')
                        ->modalContent(function (Action $action) {
                            $ids = SbMember::whereNull('eingetragen')->pluck('id')->toArray();
                            $memberTeamAktionen = new MemberTeamAktionen();
                            $html = new HtmlString(
                                Blade::render('@livewire("⚡process-sb-members", [
                                        "recordIds" => $ids, 
                                        "id" => $modalId,
                                        "memberTeamAktionen" => $memberTeamAktionen
                                    ])',
                                    [
                                        'ids' => $ids,
                                        // if this is no longer ok, look at the modal in Chrome inspector, to see how it is formed.
                                        'modalId' => "fi-" . $action->getLivewire()->getId() . "-action-" . $action->getNestingIndex(),
                                        'memberTeamAktionen' => $memberTeamAktionen
                                    ]
                                )
                            );
                            // Log::info("HTML: " . $html);
                            return $html;
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false),
                ],
            )
            ->defaultSort('eingetragen', 'asc');
    }
}
