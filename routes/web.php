<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemberController;
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
Route::resource('member', MemberController::class)
    ->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
Route::get('member/{id}/teams', [MemberController::class, 'teams'])->middleware("auth")->name("teams");
Route::get('member/{member}/withdialog', [MemberController::class, 'showWithDialog'])->middleware("auth")->name("member.showWithDialog");
