<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()// show login form
    {
        return inertia("Login");
    }
    public function store(Request $request)// submit login form
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
        $request->session()->regenerate();
        return redirect()->intended("/");
    }
    public function destroy(Request $request)// logout
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("home");
    }

    public function update(Request $request, User $user)
    {
        $req = $request->validate([
            "email" => "required|string|email",
            "password" => "required|string",
            "newpwd" => "required|string",
        ]);
        $creds = [
            "email" => $req["email"],
            "password" => $req["password"],

        ];
        if (!Auth::attempt($creds, true)) {
            throw ValidationException::withMessages([
                "email" => "Falsche Email oder Passwort"
            ]);
        }
        Auth::user()->update(["password" => $req["newpwd"]]);
        return redirect()->route("home")->with('success', 'Passwort geändert!');
    }
}
