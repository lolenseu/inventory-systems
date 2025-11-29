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
        return view('auth.register');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
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
        
        // Set role based on in_game_name using role column
        if (stripos($request->in_game_name, '_officer') !== false || 
            stripos($request->in_game_name, 'officer') !== false) {
            $user->role = 'officer';
        } else {
            $user->role = 'user';
        }
        
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
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Ensure role is set if not already
            if (!$user->role) {
                if (stripos($user->in_game_name, '_officer') !== false || 
                    stripos($user->in_game_name, 'officer') !== false) {
                    $user->role = 'officer';
                } else {
                    $user->role = 'user';
                }
                $user->save();
            }
            
            // Redirect to dashboard - role will be checked there
            return redirect('/dashboard')->with('success', 'Welcome back, ' . $user->in_game_name . '!');
        }

        return back()->with('error', 'Invalid in-game name or password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
