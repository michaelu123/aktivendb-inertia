<?php

namespace App\Traits;

use App\Models\History;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TracksHistoryTrait
{

  public function track(Model $model, $table = null, $id = null)
  {
    // Allow for overriding of table if it's not the model table
    $table = $table ?: $model->getTable();
    // Allow for overriding of id if it's not the model id
    $id = $id ?: $model->id;

    $record_old_json = json_encode($model->getOriginal());
    $record_new_json = json_encode($model->getAttributes());

    $userId = 0;
    if (null != Auth::user())
      $userId = Auth::user()->id;

    History::create([
      'reference_table' => $table,
      'reference_id' => $id,
      'user_id' => $userId,
      'record_old' => $record_old_json,
      'record_new' => $record_new_json
    ]);
  }
}