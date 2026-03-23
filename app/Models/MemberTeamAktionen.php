<?php

namespace App\Models;

use App\Mail\LeitungsBrief;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Wireable;

class MemberTeamAktionen implements Wireable
{
  protected $leitungenMap = [
    "AG Aktionen" => "ag-aktionen_leitung@groups.adfc-muenchen.de",
    "AG Codierung" => "ag-codierung_leitung@groups.adfc-muenchen.de",
    "AG IT" => "ag-it_leitung@groups.adfc-muenchen.de",
    "AG Landkreis München" => "ag-landkreis_leitung@groups.adfc-muenchen.de",
    "AG Mehrtagestouren" => "ag-mtt_leitung@groups.adfc-muenchen.de",
    "AG Navigation" => "ag-navigation_leitung@groups.adfc-muenchen.de",
    "AG RFS AnfängerInnen" => "ag-radfahrschule_anfaengerinnen_leitung@groups.adfc-muenchen.de",
    "AG RFS Fortgeschrittene" => "ag-radfahrschule_fortgeschrittene_leitung@groups.adfc-muenchen.de",
    "AG Soziales" => "ag-soziales_leitung@groups.adfc-muenchen.de",
    "AG Sternfahrt" => "ag-sternfahrt_leitung@groups.adfc-muenchen.de",
    "AG TT Mountainbike" => "ag-tagestouren_mtb_leitung@groups.adfc-muenchen.de",
    "AG TT Rennrad" => "ag-tagestouren_rennrad_leitung@groups.adfc-muenchen.de",
    "AG TT Tourenrad" => "ag-tagestouren_tourenrad_leitung@groups.adfc-muenchen.de",
    "AG Tandem" => "ag-tandem_leitung@groups.adfc-muenchen.de",
    "AG Technik" => "ag-technik_leitung@groups.adfc-muenchen.de",
    "AG Verkehr" => "ag-verkehr_leitung@groups.adfc-muenchen.de",
    "AG Verkehrsrecht" => "ag-verkehrsrecht_leitung@groups.adfc-muenchen.de",
    "Event teamName" => "event-teamName_leitung@groups.adfc-muenchen.de",
    "AG Jugend" => "jugend_leitung@groups.adfc-muenchen.de",
    "OG Feldkirchen" => "og-feldkirchen_sprecherinnen@groups.adfc-muenchen.de",
    "OG Garching" => "og-garching_sprecherinnen@groups.adfc-muenchen.de",
    "OG Gräfelfing" => "og-graefelfing_sprecherinnen@groups.adfc-muenchen.de",
    "OG Grünwald" => "og-gruenwald_sprecherinnen@groups.adfc-muenchen.de",
    "OG Höhenkirchen-Siegertsbrunn" => "og-hoehenkirchen-siegertsbrunn_sprecherinnen@groups.adfc-muenchen.de",
    "OG Ismaning" => "og-ismaning_sprecherinnen@groups.adfc-muenchen.de",
    "OG Neubiberg" => "og-neubiberg_sprecherinnen@groups.adfc-muenchen.de",
    "OG Neuried" => "og-neuried_sprecherinnen@groups.adfc-muenchen.de",
    "OG Ottobrunn" => "og-ottobrunn_sprecherinnen@groups.adfc-muenchen.de",
    "OG Planegg" => "og-planegg_sprecherinnen@groups.adfc-muenchen.de",
    "OG Putzbrunn" => "og-putzbrunn_sprecherinnen@groups.adfc-muenchen.de",
    "OG Sauerlach" => "og-sauerlach_sprecherinnen@groups.adfc-muenchen.de",
    "OG Straßlach-Dingharting" => "og-strasslach-dingharting_sprecherinnen@groups.adfc-muenchen.de",
    "OG Unterhaching" => "og-unterhaching_sprecherinnen@groups.adfc-muenchen.de",
    "OG Unterschleißheim-Oberschleißheim" => "og-unterschleissheim-oberschleissheim_sprecherinnen@groups.adfc-muenchen.de",
    "Fundraising" => "fundraising@groups.adfc-muenchen.de",
    "Standby" => null,
    "OG Oberhaching" => null,
  ];

  /** @var array<string, array<string>> */
  protected array $teamName2MemberNameAdd = [
  ];

  /** @var array<string, array<string>> */
  protected array $teamName2MemberNameDelete = [
  ];

  public function add($teamName, $memberDetails)
  {
    if (isset($this->teamName2MemberNameAdd[$teamName])) {
      $this->teamName2MemberNameAdd[$teamName][] = $memberDetails;
    } else {
      $this->teamName2MemberNameAdd[$teamName] = [$memberDetails];
    }
  }

  public function delete($teamName, $memberDetails)
  {
    if (isset($this->teamName2MemberNameDelete[$teamName])) {
      $this->teamName2MemberNameDelete[$teamName][] = $memberDetails;
    } else {
      $this->teamName2MemberNameDelete[$teamName] = [$memberDetails];
    }
  }

  public function teamNamesToSendEmailsTo(): array
  {
    $tn = [];
    foreach (array_keys($this->leitungenMap) as $teamName) {
      $add = $this->teamName2MemberNameAdd[$teamName] ?? [];
      $delete = $this->teamName2MemberNameDelete[$teamName] ?? [];
      if (!empty($add) || !empty($delete)) {
        $tn[] = $teamName;
      }
    }
    return $tn;
  }

  public function sendMailToTeamLeiter($teamName)
  {
    $add = $this->teamName2MemberNameAdd[$teamName] ?? [];
    $delete = $this->teamName2MemberNameDelete[$teamName] ?? [];
    if (str_starts_with($teamName, "OG")) {
      $anrede = "Liebe Sprecher/Sprecherinnen der " . $teamName;
    } else {
      $anrede = "Liebe Leitung der " . $teamName;
    }
    $recipient = $this->leitungenMap[$teamName] ?? null;
    if (null == $recipient) {
      return;
    }
    Mail::to($recipient)->send(new LeitungsBrief($anrede, $add, $delete));
  }


  public function aktion(SbMember $sbMember, Member $member, Team $team)
  {
    $aktion = $team->team_sbmembers->aktion;
    $emails = [];
    if ($member->email_adfc) {
      $emails[] = $member->email_adfc;
    }
    if ($member->email_private) {
      $emails[] = $member->email_private;
    }
    $emails = implode(", ", $emails);
    $details = $member->first_name . " " . $member->last_name . " ( " . $emails . " )";
    if ($aktion == "add") {
      $this->add($team->name, $details);
    }
    if ($aktion == "delete") {
      $this->delete($team->name, $details);
      $member->teams()->detach($team->id);
    }
  }

  public function toLivewire()
  {
    Log::info("tolAdd" . json_encode($this->teamName2MemberNameAdd));
    Log::info("tolDel" . json_encode($this->teamName2MemberNameDelete));
    return [
      'add' => $this->teamName2MemberNameAdd,
      'delete' => $this->teamName2MemberNameDelete,
    ];
  }

  public static function fromLivewire($value)
  {
    Log::info("froml" . json_encode($value));
    $instance = new static();
    $instance->teamName2MemberNameAdd = $value['add'] ?? [];
    $instance->teamName2MemberNameDelete = $value['delete'] ?? [];
    Log::info("fromlAdd" . json_encode($instance->teamName2MemberNameAdd));
    Log::info("fromLDel" . json_encode($instance->teamName2MemberNameDelete));
    return $instance;
  }
}
