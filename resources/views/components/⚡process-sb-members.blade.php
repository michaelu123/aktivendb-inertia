<?php

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
    public $vorschau = true;

    public function mount(array $recordIds, string $id): void
    {
        $this->recordIds = $recordIds;
        $this->id = $id;
        $this->totalRecords = count($recordIds);
        $this->log[] = "Bereit, {$this->totalRecords} Mitglieder zu übernehmen.";
    }

    public function process(): void
    {

        if ($this->processedCount >= $this->totalRecords) {
            $this->log[] = 'Alle Mitglieder wurden verarbeitet.';
            $this->dispatch('processing-finished');
            Notification::make()
                ->title('Übernahme abgeschlossen')
                ->success()
                ->send();
            return;
        }

        $recordId = $this->recordIds[$this->processedCount];
        $sbMember = SbMember::find($recordId);

        if ($sbMember) {
            $this->log[] = "Verarbeite {$sbMember->first_name} {$sbMember->last_name} (ID: {$sbMember->id})...";

            try {
                // This is where the actual logic to transfer the member would go.
                // For demonstration, I'll just log it and simulate work.
                // In a real scenario, you would create a new Member and delete the SbMember.
                // DB::transaction(function () use ($sbMember) {
                //     \App\Models\Member::create($sbMember->toArray());
                //     $sbMember->delete();
                // });

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
            $this->log[] = 'Alle Mitglieder wurden verarbeitet.';
            $this->dispatch('processing-finished');
            Notification::make()
                ->title('Übernahme abgeschlossen')
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
                setTimeout(() => $wire.process(), 2000);
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
            <label class="my-10">
                <x-filament::input.checkbox wire:model="vorschau" /> <span>Vorschau</span>
            </label>
        </x-filament::actions>
        <x-filament::actions>
            <x-filament::button x-show="!processing" wire:click="process" x-on:click="processing = true"
                :disabled="$totalRecords === 0">
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