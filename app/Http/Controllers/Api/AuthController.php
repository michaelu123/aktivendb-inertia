<?php

namespace App\Http\Controllers\Api;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        if (
            !Auth::attempt(
                $request->validate([
                    "email" => "required|string|email",
                    "password" => "required|string",
                ]),
                true
            )
        ) {
            throw ValidationException::withMessages([
                "email" => "Falsche Email oder Passwort"
            ]);
        }
        $token = $request->user()->createToken("token", ['*'], now()->plus(hours: 4));

        return ['token' => $token->plainTextToken];
    }

}
