<?php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia("Index/Index");
})->name("index.index");

Route::resource('member', MemberController::class)
    ->only(['index']);