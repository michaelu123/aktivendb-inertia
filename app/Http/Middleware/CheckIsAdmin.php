<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class CheckIsAdmin
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if ($request->path() != "auth/login") {
      $user = $request->user();
      if (!$user->isAdmin) {
        return response("must be admin", 403);
      }
    }
    return $next($request);
  }
}