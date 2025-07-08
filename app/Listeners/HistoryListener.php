<?php

namespace App\Listeners;

use App\Events\MemberUpdated;
use App\Events\TeamMemberUpdated;
use App\Events\TeamUpdated;
use App\Traits\TracksHistoryTrait;

class HistoryListener
{
  use TracksHistoryTrait;

  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   *
   * @param  MemberUpdated  $event
   * @return void
   */
  public function onMemberUpdate(MemberUpdated $event)
  {
    $this->track($event->member);
  }

  /**
   * Handle the event.
   *
   * @param  TeamUpdated  $event
   * @return void
   */
  public function onTeamUpdate(TeamUpdated $event)
  {
    $this->track($event->team);
  }

  /**
   * Handle the event.
   *
   * @param  TeamMemberUpdated  $event
   * @return void
   */
  public function onTeamMemberUpdate(TeamMemberUpdated $event)
  {
    $this->track($event->team_member);
  }

  /**
   * Register the listeners for the subscriber.
   *
   * @param  \Illuminate\Events\Dispatcher  $events
   * @return array
   */
  public function subscribe($events)
  {
    $events->listen(
      'App\Events\MemberUpdated',
      'App\Listeners\HistoryListener@onMemberUpdate'
    );

    $events->listen(
      'App\Events\TeamUpdated',
      'App\Listeners\HistoryListener@onTeamUpdate'
    );

    $events->listen(
      'App\Events\TeamMemberUpdated',
      'App\Listeners\HistoryListener@onTeamMemberUpdate'
    );
    return [];
  }
}
