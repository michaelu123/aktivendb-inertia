<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamsController;
use App\Http\Controllers\Api\TeamMembersController;
use App\Http\Controllers\Api\MembersController;
use App\Http\Controllers\Api\AuthController;


Route::post('/auth/login', [AuthController::class, 'authenticate'])->name("api.login");


$router->group(["prefix" => "api", "middleware" => "auth:sanctum"], function () use ($router) {
    /**
     * Routes for resource member
     */
    $router->get('members', [MembersController::class, 'all'])->name("api.members.all");
    $router->get('member/{id}', [MembersController::class, 'get'])->name("api.members.get");
    # $router->post('member', [MembersController::class, 'add'])->name("api.members.add"); # not used by aktdb_tool
    $router->put('member/{id}', [MembersController::class, 'put'])->name("api.members.put");
    $router->delete('member/{id}', [MembersController::class, 'remove'])->name("api.members.remove");


    /**
     * Routes for resource project-team
     */
    $router->get('project-teams', [TeamsController::class, 'all'])->name("api.teams.all");
    $router->get('project-team/{id}', [TeamsController::class, 'get'])->name("api.teams.get");
    // $router->put('project-team/{id}', [TeamsController::class, 'put'])->name("api.teams.put"); # not used by aktdb_tool

    // $router->post('project-team-member', [TeamMembersController::class, 'add'])->name("api.tm.add"); # not used by aktdb_tool
    $router->delete('project-team-member/{id}', [TeamMembersController::class, 'remove'])->name("api.tm.remove");
});
