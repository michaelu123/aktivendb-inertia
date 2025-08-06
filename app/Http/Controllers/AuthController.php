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

    public function addUser(Request $request)
    {
        $v = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'member_id' => 'required|min:0',
        ]);
        $users = User::where("email", $v["email"])->get();
        if ($users->count() > 0) {
            $user = $users[0];
            $user->update(['password' => $v['password']]);
            return redirect()->route('member.index')
                ->with('success', 'Passwort geändert!');
        }
        $user = User::create($v);
        return redirect()->route('member.index')
            ->with('success', 'Account created!');
    }
}
