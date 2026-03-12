<?php

use Livewire\Component;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Checkbox;
use App\Models\SbMember;
use App\Models\Member;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Request;

new class extends Component implements HasSchemas {
    // noinspection PhpUnusedAliasInspection
    /** @use \Filament\Schemas\Concerns\InteractsWithSchemas */
    use \Filament\Schemas\Concerns\InteractsWithSchemas;
    protected const _TRAITS = [\Filament\Schemas\Concerns\InteractsWithSchemas::class];

    public array $data = [];
    protected $memberId;

    public function mount(Request $request, $id): void
    {
        $member = Member::with("teams")->find((int) $id);
        $this->memberId = $member->id;
        $memberData = $member->toArray();
        $this->form->fill($memberData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label("Vorname")
                    ->required(),
                TextInput::make('last_name')
                    ->label("Nachname")
                    ->required(),
                TextInput::make('email_adfc')
                    ->label("Email ADFC")
                    ->email(),
                TextInput::make('email_private')
                    ->label("Email privat")
                    ->email(),
                TextInput::make('phone_primary')
                    ->label('Telefon1')
                    ->tel(),
                TextInput::make('phone_secondary')
                    ->label('Telefon2')
                    ->tel(),
                TextInput::make('address')
                    ->label('Postleitzahl')
                    ->numeric(),
                TextInput::make('adfc_id')
                    ->label("Mitgliedsnummer")
                    ->rules("digits:8"),
                TextInput::make('gender')
                    ->label('Geschlecht'),
                TextInput::make('interests')
                    ->label('Interessen'),
            ])->statePath('data');
    }

    public function create(): void
    {
        SbMember::create($this->form->getState());
        redirect()->route('sbdanke')->with('msg', "Sie erhalten in Kürze eine E-Mail.");
    }
}
?>

<x-filament::section class="max-w-7xl mx-auto items-center justify-center">
    <x-slot name="heading">
        <div class="flex flex-row justify-between items-center">
            <p class="lg:text-5xl text-2xl">Ihre Daten mit Bitte um Bestätigung</p>
            <img src="/ADFC_MUENCHEN.PNG" alt="">
        </div>
    </x-slot>
    <div>
        <p class="mb-10">
            Mit diesem Formular ...
        </p>
        <form wire:submit="create">
            {{ $this->form }}
            <x-filament::button type="submit" class="mt-4">
                Abschicken
            </x-filament::button>
        </form>

        <x-filament-actions::modals />
    </div>
</x-filament::section>