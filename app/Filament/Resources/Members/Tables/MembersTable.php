<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email_adfc')
                    ->searchable(),
                TextColumn::make('email_private')
                    ->searchable(),
                IconColumn::make('responded_to_questionaire')
                    ->sortable()
                    ->boolean(),
                TextColumn::make('responded_to_questionaire_at')
                    ->sortable()
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('sbsenden')
                        ->label("Serienbrief versenden")
                        ->icon('heroicon-o-envelope')
                        ->deselectRecordsAfterCompletion()
                        ->modalContent(function (Collection $members, BulkAction $action) {
                            $ids = $members->pluck('id')->toArray();
                            return new HtmlString(
                                Blade::render('@livewire("⚡sende-sb-emails", ["recordIds" => $ids, "id" => $modalId])', [
                                    'ids' => $ids,
                                    // if this is no longer ok, look at the modal in Chrome inspector, to see how it is formed.
                                    'modalId' => "fi-" . $action->getLivewire()->getId() . "-action-" . $action->getNestingIndex(),
                                ])
                            );
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false),
                ]),
            ]);
    }
}
