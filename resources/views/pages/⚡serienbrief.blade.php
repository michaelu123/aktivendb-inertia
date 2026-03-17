<?php

use Livewire\Component;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\TextInput;
use App\Models\SbMember;
use App\Models\Member;
use App\Models\Team;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

new class extends Component implements HasSchemas {
    use InteractsWithSchemas;
    protected const _TRAITS = [InteractsWithSchemas::class]; // to silence unused warning

    public array $data = [];
    public $member_id;


    public $h1R = "Rückmeldung erforderlich - Jährliche Aktualisierung unserer Aktiven-Datenbank";
    public $pR = <<<EOD
                Wir möchten gerne möglichst korrekte Daten in unserer Datenbank von den aktiven Mitgliedern haben.
                Deshalb bitten wir Dich jährlich, die über Dich gespeicherten Daten zu überprüfen und sie ggfs. zu
                ändern. Teile uns bitte vor allem mit, ob Du überhaupt damit einverstanden bist, dass Deine Daten
                gespeichert werden, und ob Du Dich (noch) als aktives Mitglied betrachtest.<br><br>

                Falls Du mit der Speicherung nicht einverstanden bist, löschen wir Deine Daten. Falls Du aktuell in
                keiner AG / OG aktiv bist und auch nicht für Einsätze zur Verfügung stehst, bleiben Deine Daten in der
                DB, Dein Eintrag wird aber auf inaktiv gesetzt. Die einzige andere Pflichtangabe ist Dein Name.<br><br>

                <strong>Bitte klicke am Ende des Fragebogens AUF JEDEN FALL auf Abschicken</strong>, auch dann, wenn Du
                nichts geändert hast, damit wir wissen, dass wir die aktuelle Daten von Dir haben.
                Wenn wir von Dir keine Bestätigung erhalten, werden wir deine Daten nach einiger Zeit in der
                Datenbank und Emailverteilern etc. löschen.<br><br>
                EOD;
    public $okR = "Bitte klicke auf Ja, wenn Du mit der Speicherung deiner Daten in unserer AktivenDB einverstanden bist. Wenn Du auf Nein klickst, <strong>klicke bitte am Ende des Formulars auf Senden!</strong> Sonst erfahren wir nichts von Deiner Entscheidung.";

    public $h1E = "Erstanmeldung - Deine Daten für unsere Aktiven-Datenbank";
    public $pE = <<<EOD
                Wir bitten Dich, Deine Daten für unsere AktivenDB einzugeben. Außer Vor- und Nachnamen und der 
                Zustimmung zur Speicherung sind aber keine Angaben zwingend. Wenn Du überhaupt nicht willst, 
                daß Deine Daten gespeichert werden, schickst Du das Formular nicht ab.
                EOD;
    public $okE = "Bitte klicke auf Ja, um zu bestätigen, daß Du mit der Speicherung deiner Daten in unserer AktivenDB einverstanden bist.";
    public $h1, $p, $ok;

    public string $idEnc;

    public function mount(Request $request, $id): void
    {
        Log::info("id1 " . $id);
        if (strlen($id) > 20) { // TODO
            $id = Crypt::decryptString($id);
            Log::info("id2 " . $id);
        }
        $this->idEnc = Crypt::encryptString($id); // TODO

        if ($id != 0) {
            $member = Member::with("teams")->find((int) $id);
            Log::info("member " . $member);
            $id = $member != null ? $member->id : 0;
        }
        if ($id == 0) {
            $this->member_id = 0;
            $memberData = [];
            $this->h1 = $this->h1E;
            $this->p = $this->pE;
            $this->ok = $this->okE;
        } else {
            $memberData = $member->toArray();
            $gender = $memberData['gender'];
            if (isset($gender)) {
                $gender = [$gender => $gender];
            }
            $memberData['gender'] = $gender;
            $memberData['teams'] = $member->teams->pluck('id', "name")->toArray();
            $this->h1 = $this->h1R;
            $this->p = $this->pR;
            $this->ok = $this->okR;
        }
        $this->form->fill($memberData);
    }

    public function form(Schema $schema): Schema
    {
        /** @var Collection<string> $teams */
        $teams = Team::orderBy('name')->get()->mapWithKeys(function (Team $team): array {
            return [$team->id => $team->name];
        });
        $x = $teams->search("Vorstand");
        unset($teams[$x]);
        $x = $teams->search("Standby");
        $teams[$x] = "in keiner AG aktiv, stehe aber für Einsätze zur Verfügung";
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label("Vorname")
                    ->required(),
                TextInput::make('last_name')
                    ->label("Nachname")
                    ->required(),
                Radio::make("speicherungok")
                    ->belowLabel(new HtmlString($this->ok))
                    ->label("Mit Speicherung einverstanden?")
                    ->options([1 => "Ja", 0 => "Nein"])
                    ->validationMessages([
                        "required" => "Bitte Ja oder Nein auswählen."
                    ])
                    ->required(),
                Radio::make("aktiv")
                    ->hidden($this->member_id == 0)
                    ->label("Aktives Mitglied?")
                    ->belowLabel(new HtmlString("Wenn Du Dich (noch) als aktives Mitglied siehst, klicke bitte auf Ja. Wenn Du auf Nein klickst, kannst Du noch Deine Daten aktualisieren, aber <strong>bitte klicke am Ende des Formulars auf Senden!</strong>"))
                    ->options([1 => "Ja", 0 => "Nein"])
                    ->validationMessages([
                        "required" => "Bitte Ja oder Nein auswählen."
                    ])
                    ->required(),
                CheckboxList::make('gender')
                    ->label('Geschlecht')
                    ->belowLabel("M oder W oder freilassen")
                    ->options(["M" => "M", "W" => "W"]),
                TextInput::make('birthday')
                    ->label("Geburtsjahr")
                    ->belowLabel("Z.B. 1965, 2001")
                    ->rules("digits:4")
                    ->validationMessages([
                        "digits" => "Bitte 4 Ziffern eingeben."
                    ])
                    ->integer(),
                TextInput::make('address')
                    ->label('Postleitzahl')
                    ->belowLabel("Wenn wir Deine PLZ wissen, können wir Dich evtl. gezielt auf Aktionen in Deiner Nähe ansprechen.")
                    ->validationMessages([
                        "digits" => "Bitte 5 Ziffern eingeben."
                    ])
                    ->rules("digits:5")
                    ->integer(),
                TextInput::make('email_adfc')
                    ->label("ADFC Email-Adresse")
                    ->endsWith("@adfc-muenchen.de")
                    ->validationMessages([
                        "ends_with" => "Die E-Mail-Adresse muss auf @adfc-muenchen.de enden."
                    ])
                    ->belowLabel("Falls Du eine Email-Adresse hast, die auf @adfc-muenchen.de endet, gib sie bitte hier an.")
                    ->email(),
                TextInput::make('email_private')
                    ->label("Eigene Email-Adresse")
                    ->doesntEndWith("@adfc-muenchen.de")
                    ->validationMessages([
                        "doesnt_end_with" => "Bitte keine @adfc-muenchen.de Adresse hier eingeben."
                    ])
                    ->belowLabel("Falls Du eine Email-Adresse hast, die nicht auf @adfc-muenchen.de endet, gib sie bitte hier an.")
                    ->email(),
                TextInput::make('phone_primary')
                    ->label('Telefonnummer 1')
                    ->belowLabel("Z.B. eine Festnetznummer")
                    ->validationMessages([
                        "regex" => "Bitte eine Telefonnummer eingeben."
                    ])
                    ->tel(),
                TextInput::make('phone_secondary')
                    ->label('Telefonnummer 2')
                    ->belowLabel("Z.B. eine Mobilfunknummer")
                    ->validationMessages([
                        "regex" => "Bitte eine Telefonnummer eingeben."
                    ])
                    ->tel(),
                CheckboxList::make('teams')->options($teams)
                    ->label("AGs und OGs")
                    ->belowLabel($this->member_id == 0
                        ? "Bitte gib an, für welche Abeitsgruppen (AGs) und/oder Ortsgruppen (OGs) Du Dich interessierst."
                        : "Bitte gib an, in welchen Arbeitsgruppen (AGs)aktiv bist. Wenn Du aktiv in den AGs Tagestouren (TT) oder Radfahrschule (RFS) bist, mach bitte ein Häkchen bei der/den zutreffenden Untergruppe(n)."),
                TextInput::make('interests')
                    ->label('Interessen')
                    ->belowLabel("Bitte gib Deine Interessen an."),
                TextInput::make('adfc_id')
                    ->label("ADFC Mitgliedsnummer")
                    ->rules("digits:8"),
            ])->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $genderCnt = count($data['gender']);
        $data['gender'] = $genderCnt == 1 ? $data['gender'][0] : null;

        if ($this->member_id == 0) {
            $m = Member::where("first_name", $data["first_name"])->where("last_name", $data["last_name"])->first();
            if (!isset($m)) {
                $data["name"] = $data["last_name"] . ", " . $data["first_name"];
                $data["aktiv"] = 0;
                $m = Member::create($data);
                $this->member_id = $m->id;
            } else {
                $this->member_id = $m->id;
                $data["aktiv"] = $m->active;
            }
        }
        Log::info("member_id " . $this->member_id);
        $data['member_id'] = $this->member_id;
        Log::info($data);
        SbMember::create($data)->teams()->attach($data['teams']);
        redirect()->route('sbdanke');
    }
}
?>

<x-filament::section class="max-w-7xl mx-auto items-center justify-center">
    <x-slot name="heading">
        <div class="flex flex-row justify-between items-center">
            <p class="lg:text-5xl text-2xl">
                {{ $this->member_id == 0 ? "Erstanmeldung" : "Deine Daten mit Bitte um Bestätigung" }}
            </p>
            <img src="/ADFC_MUENCHEN.PNG" alt="">
        </div>
    </x-slot>
    <div>
        <p>{{ $this->idEnc }}</p>
        <div class="mb-10">
            <h1 class="lg:text-5xl text-2xl mb-10">{{ $this->h1  }}</h1>
            <p>{{ new HtmlString($this->p) }}</p>
        </div>
        <form wire:submit="create">
            {{ $this->form }}
            <x-filament::button type="submit" class="mt-4">
                Abschicken
            </x-filament::button>
            <p>Wenn Du nach dem Abschicken nicht gleich auf eine neue Seite weitergeleitet wirst, hat im Formular etwas
                nicht gestimmt.
                Bitte scrolle nach oben und achte auf rot markierte Fehler.</p>
        </form>

        <x-filament-actions::modals />
    </div>
</x-filament::section>