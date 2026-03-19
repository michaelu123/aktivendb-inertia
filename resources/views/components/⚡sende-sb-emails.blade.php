<?php

use App\Mail\SerienBrief;
use Livewire\Component;
use Filament\Notifications\Notification;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

new class extends Component {
    public array $recordIds = [];
    public int $processedCount = 0;
    public array $log = [];
    public int $totalRecords = 0;
    public string $id;
    public $vorschau = true;

    public function mount(array $recordIds, string $id): void
    {
        $this->recordIds = $recordIds;
        $this->id = $id;
        $this->totalRecords = count($recordIds);
        $this->log[] = "Bereit, Serienbrief an {$this->totalRecords} Mitglieder zu senden.";
    }

    public function processRestart(): void
    {
        $this->processedCount = 0;
        $this->log = ["Bereit, Serienbrief an {$this->totalRecords} Mitglieder zu senden."];
        $this->process();
    }

    public function process(): void
    {
        if ($this->processedCount >= $this->totalRecords) {
            $this->log[] = 'Alle Serienbriefe wurden verschickt.';
            $this->dispatch('processing-finished');
            Notification::make()
                ->title('Senden der Emails beendet')
                ->success()
                ->send();
            return;
        }

        $recordId = $this->recordIds[$this->processedCount];
        $member = Member::find($recordId);

        if ($member) {
            $this->log[] = "Sende Email an {$member->first_name} {$member->last_name})...";

            try {
                // do work here
                // if (!str_ends_with($this->email, "@adfc-muenchen.de")) {
                //     return;
                // } // TODO 
                $recipients = [];
                if ($member->email_adfc) {
                    $recipients[] = $member->email_adfc;
                }
                if ($member->email_private) {
                    $recipients[] = $member->email_private;
                }
                $recipients = implode(',', $recipients);
                if (empty($recipients)) {
                    $this->log[] = "'{$member->first_name} {$member->last_name}' hat keine Email-Adresse.";
                } else if ($this->vorschau) {
                    $this->log[] = "an '{$recipients}' wäre gesendet worden.";
                } else {
                    Mail::to($recipients)->send(new SerienBrief($recordId));
                    $this->log[] = "an '{$recipients}' gesendet.";
                }
            } catch (\Exception $e) {
                Log::error("Fehler beim Senden der Email: " . $e->getMessage());
                $this->log[] = "-> FEHLER beim Senden der Email: " . $e->getMessage();
            }
        } else {
            $this->log[] = "-> Mitglied mit ID {$recordId} nicht gefunden. Übersprungen.";
        }

        $this->processedCount++;

        if ($this->processedCount < $this->totalRecords) {
            $this->dispatch('next-item');
        } else {
            $this->log[] = 'Emails an alle Mitglieder gesendet.';
            $this->dispatch('processing-finished');
            Notification::make()
                ->title($this->vorschau ? 'Vorschau abgeschlossen' : 'Emails senden abgeschlossen')
                ->success()
                ->send();
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
            Gesendet: {{ $processedCount }} / {{ $totalRecords }}
        </p>
        @if($processedCount < $totalRecords && $this->processedCount > 0)
            <div class="mt-2">
                <x-filament::loading-indicator class="h-5 w-5" />
            </div>
        @endif

        <x-filament::actions>
            <label class="mb-4">
                <x-filament::input.checkbox wire:model="vorschau" /> <span>Vorschau</span>
            </label>
        </x-filament::actions>
        <x-filament::actions>
            <x-filament::button x-show="!processing" wire:click="processRestart" x-on:click="processing = true"
                :disabled="$totalRecords === 0">
                Start
            </x-filament::button>
            <x-filament::button x-show="processing" disabled class="cursor-wait">
                <x-filament::loading-indicator class="h-5 w-5 mr-2" />
                Senden...
            </x-filament::button>
            <x-filament::button color="secondary" x-on:click="$dispatch('close-modal', { id: '{{ $this->id }}' })">
                Schließen
            </x-filament::button>
        </x-filament::actions>
    </x-filament::section>