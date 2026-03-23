<?php

use App\Models\Member;
use App\Models\MemberTeamAktionen;
use Livewire\Component;
use Filament\Notifications\Notification;
use App\Models\SbMember;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public array $recordIds = [];
    public int $processedCount = 0;
    public array $log = [];
    public int $totalRecords = 0;
    public string $id;
    public MemberTeamAktionen $memberTeamAktionen;
    public $teamNamesToSendEmailsTo = [];
    public int $phase = 0;

    public function mount(array $recordIds, string $id, MemberTeamAktionen $memberTeamAktionen): void
    {
        $this->memberTeamAktionen = $memberTeamAktionen;
        $this->recordIds = $recordIds;
        $this->id = $id;
        $this->totalRecords = count($recordIds);
        $this->log[] = "Bereit, {$this->totalRecords} Mitglieder zu übernehmen.";
    }

    public function process(): void
    {
        if ($this->processedCount >= $this->totalRecords) {
            $this->log[] = 'Alle Serienbrief-Antworten wurden verarbeitet.';
            $this->dispatch('processing-finished');
            Notification::make()
                ->title('Übernahme abgeschlossen')
                ->success()
                ->send();
            return;
        }

        if ($this->phase == 0) {
            $recordId = $this->recordIds[$this->processedCount];
            $sbMember = SbMember::with('teams')->find($recordId);
            $member = Member::find($sbMember->member_id);

            if ($sbMember) {
                $this->log[] = "Übernehme {$sbMember->first_name} {$sbMember->last_name} (ID: {$sbMember->id})...";

                try {
                    $teams = $sbMember->teams()->get();
                    foreach ($teams as $team) {
                        Log::info("Member " . $sbMember->first_name . " " . $sbMember->last_name . ":  Team " . $team->name . " (" . $team->team_sbmembers->aktion . ")" . " Email " . $team->email);
                        $this->memberTeamAktionen->aktion($sbMember, $member, $team);
                    }
                    $sbMember->update(["eingetragen" => now()]);
                    $this->log[] = "-> '{$sbMember->first_name} {$sbMember->last_name}' erfolgreich übernommen.";
                } catch (\Exception $e) {
                    Log::error("Fehler bei der Übernahme von SbMember ID {$sbMember->id}: " . $e->getMessage());
                    $this->log[] = "-> FEHLER bei der Übernahme von '{$sbMember->first_name} {$sbMember->last_name}': " . $e->getMessage();
                }
            } else {
                $this->log[] = "-> Mitglied mit ID {$recordId} nicht gefunden. Übersprungen.";
            }

            $this->processedCount++;

            if ($this->processedCount < $this->totalRecords) {
                $this->dispatch('next-item');
            } else {
                $this->processedCount = 0;
                $this->phase = 1;
                $this->teamNamesToSendEmailsTo = $this->memberTeamAktionen->teamNamesToSendEmailsTo();
                $this->totalRecords = count($this->teamNamesToSendEmailsTo);
                if ($this->totalRecords > 0) {
                    $this->log[] = 'E-Mails an die Leitungen werden gesendet.';
                }
                $this->dispatch('next-item');
            }
        } else { // second phase, send emails to Team leaders
            $teamName = $this->teamNamesToSendEmailsTo[$this->processedCount];
            $this->log[] = "Sende E-Mail an {$teamName}";
            try {
                $this->memberTeamAktionen->sendMailToTeamLeiter($teamName);
            } catch (\Exception $e) {
                Log::error("Fehler beim E-Mail senden: " . $e->getMessage());
                $this->log[] = "-> FEHLER beim Senden der E-Mail: " . $e->getMessage();
            }
            $this->processedCount++;

            $this->dispatch('next-item');
        }
    }
};
?>

<div x-data="{
        processing: false,
        init() {
            $wire.on('next-item', () => {
                // Use a small timeout to allow UI to update before next request
                setTimeout(() => $wire.process(), 10);
            });
            $wire.on('processing-finished', () => {
                this.processing = false;
                // You can uncomment this to close the modal automatically after 2 seconds
                // setTimeout(() => $dispatch('close-modal', { id: '{{ $this->id }}' }), 2000);
            });
        }
    }">

    <x-filament::section>
        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-y-auto" style="max-height: 400px;">
            <pre class="whitespace-pre-wrap font-mono text-sm">
                @foreach($log as $line){{ $line . "\n" }}@endforeach
            </pre>
        </div>
        <p class="mt-2 mb-10 text-sm text-gray-500 dark:text-gray-400">
            Verarbeitet: {{ $processedCount }} / {{ $totalRecords }}
        </p>
        @if($processedCount < $totalRecords && $this->processedCount > 0)
            <div class="mt-2">
                <x-filament::loading-indicator class="h-5 w-5" />
            </div>
        @endif

        <x-filament::actions>
            <x-filament::button x-show="!processing" wire:click="process" x-on:click="processing = true"
                :disabled="$totalRecords === 0 || $phase == 1">
                Start
            </x-filament::button>
            <x-filament::button x-show="processing" disabled class="cursor-wait">
                <x-filament::loading-indicator class="h-5 w-5 mr-2" />
                Verarbeite...
            </x-filament::button>
            <x-filament::button color="secondary" x-on:click="$dispatch('close-modal', { id: '{{ $this->id }}' })">
                Schließen
            </x-filament::button>
        </x-filament::actions>
    </x-filament::section>