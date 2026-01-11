<?php
namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

trait RESTActions
{


  public function all(Request $request)
  {
    $m = self::MODEL;
    return $this->respond(Response::HTTP_OK, $m::all());
  }

  public function get(Request $request, $id)
  {
    $m = self::MODEL;
    $model = $m::find($id);
    if (is_null($model)) {
      return $this->respond(Response::HTTP_NOT_FOUND);
    }
    return $this->respond(Response::HTTP_OK, $model);
  }

  public function add(Request $request)
  {
    $m = self::MODEL;
    $v = null;
    if (method_exists($this, 'customValidator')) {
      $validator = $this->customValidator($request);
      if ($validator->fails()) {
        return $this->respond(Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
      }
      $v = $validator->validated();
    } else {
      $v = $request->validate($m::$rules);
    }
    return $this->respond(Response::HTTP_CREATED, ($m::create($v))->fresh());
  }

  public function put(Request $request, $id)
  {
    $m = self::MODEL;
    $v = null;
    if (method_exists($this, 'customValidator')) {
      $validator = $this->customValidator($request, $id);
      if ($validator->fails()) {
        return $this->respond(Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
      }
      $v = $validator->validated();
    } else {
      $v = $request->validate($m::$rules);
    }
    $model = $m::find($id);
    if (is_null($model)) {
      return $this->respond(Response::HTTP_NOT_FOUND);
    }
    $all = $request->all();
    $model->update($all);  // should be $v, must adapt $rules
    return $this->respond(Response::HTTP_OK, $model);
  }

  public function remove(Request $request, $id)
  {
    $m = self::MODEL;
    if (is_null($m::find($id))) {
      return $this->respond(Response::HTTP_NOT_FOUND);
    }
    $m::destroy($id);
    return $this->respond(Response::HTTP_NO_CONTENT);
  }

  protected function respond($status, $data = [])
  {
    return response()->json($data, $status)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

}
