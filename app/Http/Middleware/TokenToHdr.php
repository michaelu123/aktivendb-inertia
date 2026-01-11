<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class TokenToHdr
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $token = $request->query("token", "");
    if ($token) {
      $request->headers->add(["Authorization" => "Bearer " . $token]);
    }
    return $next($request);
  }
}