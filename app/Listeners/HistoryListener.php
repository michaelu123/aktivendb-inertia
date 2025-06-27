<?php

namespace App\Listeners;

use App\Events\MemberUpdated;
use App\Events\ProjectTeamMemberUpdated;
use App\Events\ProjectTeamUpdated;
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
   * @param  ProjectTeamUpdated  $event
   * @return void
   */
  public function onProjectTeamUpdate(ProjectTeamUpdated $event)
  {
    $this->track($event->project_team);
  }

  /**
   * Handle the event.
   *
   * @param  ProjectTeamMemberUpdated  $event
   * @return void
   */
  public function onProjectTeamMemberUpdate(ProjectTeamMemberUpdated $event)
  {
    $this->track($event->project_team_member);
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
      'App\Events\ProjectTeamUpdated',
      'App\Listeners\HistoryListener@onProjectTeamUpdate'
    );

    $events->listen(
      'App\Events\ProjectTeamMemberUpdated',
      'App\Listeners\HistoryListener@onProjectTeamMemberUpdate'
    );
    return [];
  }
}
