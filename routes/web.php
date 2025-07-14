<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia("Homeview");
})->name("home");

Route::get('login', [AuthController::class, 'create'])->name("login");
Route::post('login', [AuthController::class, 'store'])->name("login.store");
Route::put('login/chgpwd', [AuthController::class, 'update'])->name("login.chgpwd");
Route::delete("login", [AuthController::class, 'create']);
Route::get('logout', [AuthController::class, 'destroy'])->name("logout");

Route::post('member/storetm', [MemberController::class, 'storeTM'])->middleware("auth")->name("member.storetm");
Route::put('member/updatetm', [MemberController::class, 'updateTM'])->middleware("auth")->name("member.updatetm");
Route::put('member/destroytm/{id}', [MemberController::class, 'destroyTM'])->middleware("auth")->name("member.destroytm");
Route::resource('member', MemberController::class)
    ->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
Route::get('member/{id}/teams', [MemberController::class, 'teams'])->middleware("auth")->name("teams");
Route::get('member/{member}/withdialog', [MemberController::class, 'showWithDialog'])->middleware("auth")->name("member.showWithDialog");
Route::post('member/{member}/history', [MemberController::class, 'indexWithHistory'])->middleware("auth")->name("member.indexWithHistory");

Route::post('team/storetm', [TeamController::class, 'storeTM'])->middleware("auth")->name("team.storetm");
Route::put('team/updatetm', [TeamController::class, 'updateTM'])->middleware("auth")->name("team.updatetm");
Route::delete('team/destroytm/{id}', [TeamController::class, 'destroyTM'])->middleware("auth")->name("team.destroytm");
Route::resource('team', TeamController::class)
    ->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
Route::get('team/{id}/members', [TeamController::class, 'teams'])->middleware("auth")->name("members");
Route::get('team/{team}/withdialog', [TeamController::class, 'showWithDialog'])->middleware("auth")->name("team.showWithDialog");
Route::post('team/{team}/history', [TeamController::class, 'indexWithHistory'])->middleware("auth")->name("team.indexWithHistory");

Route::get('history', [HistoryController::class, 'show'])->middleware("auth")->name("history.show");
Route::post('history', [HistoryController::class, 'showWithHistory'])->middleware("auth")->name("history.showWithHistory");
