<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('register');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'in_game_name' => 'required|unique:users,in_game_name',
            'in_game_id' => 'required|unique:users,in_game_id',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = new User();
        $user->in_game_name = $request->in_game_name;
        $user->in_game_id = $request->in_game_id;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/login')->with('success', 'Account created successfully! Please log in.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'in_game_name' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'in_game_name' => $request->in_game_name,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('success', 'Welcome back, ' . Auth::user()->in_game_name . '!');
        }

        return back()->with('error', 'Invalid in-game name or password.');
    }

    public function logout(Request $request)
    {
        $name = Auth::user()->in_game_name ?? 'User';
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');

    }
}
